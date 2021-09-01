/*

The public JS code for the map.
Authors:
* Thijs Brentjens (thijs@brentjensgeoict.nl)
* Paul van Buuren (paul@wbvb.nl)


*/

(function (jQuery) {
  'use strict';

  class HTMLItemsMap {

    // see end of this file for the config object
    constructor(config) {
      // all options untill Object.assign can be set using the config object
      // if not provided in the config object, use the defaults below:
      this.mapItemsContainerId = "map-items";
      this.mapItemClass = "map-item";
      this.mapClass = "archives-map";
      this.clustering = true;
      this.iconSpiderfiedOpacity = 0.75;
      this.typeLegendLabels = {
        "onbekend": "Onbekend",
        "community": "Community",
        "lab": "Datalab",
        "portaal": "Portaal",
        "strategie": "Strategie",
        "visualisatie": "Visualisatie",
      }
      // icon (wxh): 30 x 40
      // best is to make nice numbers for ratio 3:4
      // for the icons:
      this.iconHeight = 32;
      this.iconWidth = Math.round(0.75 * this.iconHeight);

      // end of the options though the config object

      // Overrule config defaults, for now for all properties
      Object.assign(this, config);

      // the properties below are required for the map to work properly, don't change these
      this.mapElementId = this.mapItemsContainerId + "-mapObject";
      this.typeFilterControlTxtId = "typeFilterControlTxt";
      this.typeFilterControlContent = "";
      // the leaflet map object:
      this._map = false;

      // for the data layers:
      this.pointsLayer;
      this.unclusteredLayers = [];
      this.clusterLayer = {};
      this.features = [];
      this.types = {};

      this.baseIcon = L.Icon.extend({
        options: {
          // PvB: ik heb de iconUrl aangepast en ervoor gezorgd dat der geen 404 meer
          // optreedt.
          // TB: ik heb het nog iets verder aangepast: rekening houden met een langer pad (bij mij draait deze installatie bijvoorbeeld op http://...domein../led/). De siteurl wordt door WP weggeschreven in een javascript object via de public class: public/class-initiatieven-kaart-public.php
          shadowUrl: `${Utils.siteurl}/wp-content/plugins/ictuwp-plugin-initiatievenkaart/public/css/images/marker-shadow.svg`,
          iconSize: [this.iconWidth, this.iconHeight],
          iconAnchor: [this.iconWidth / 2, this.iconHeight],
          // shadow: 40 x 40
          // shadowsize image is sqaure now, so 2x iconHeight
          shadowSize: [this.iconHeight, this.iconHeight],
          shadowAnchor: [this.iconWidth / 3, this.iconHeight],
          popupAnchor: [0, -1 * this.iconHeight]
        }
      })

      this.unspiderfied = false;

    }

    getLMap() {
      return this._map;
    }

    setLMap(mapObject) {
      this._map = mapObject;
    }

    initMap() {
      const _self = this;
      // first: detect if there are map elements (a container) available in the HTML, if not: just stop
      if (jQuery("." + this.mapItemClass).length == 0) {
        return false;
      }

      // init the map if it is not available yet
      if (!this.getLMap()) {
        // create an HTML element for the map if not available
        if (jQuery("#" + this.mapElementId).length == 0) {
          const _self = this;
          // append after the container
          const mapDivHtml = `<div id="${this.mapElementId}" class="${this.mapClass}" tabindex="0" aria-label="Kaart met initiatieven"></div>`;
          jQuery("#" + this.mapItemsContainerId).after(mapDivHtml);

          // create the toggle map/list button
          const toggleListButton = Object.assign(document.createElement('button'), {
            textContent: 'Toon de lijst',
            id: "toggleListMapButton",
            title: "Wissel tussen het tonen van de kaart en de lijst",
            onclick(ev) {
              _self.toggleListMap();
            }
          });
          jQuery("#" + this.mapItemsContainerId).before(toggleListButton);
          // hide the HTML list with the map items
          jQuery("#" + this.mapItemsContainerId).hide();
        }

        // initial zoom will be overwritten by the bounds of the data layer
        const mapObject = L.map(this.mapElementId, {
          'maxZoom': 18,
          // scrollWheelZoom: true,
          // enable the plugin for touch controls and to avoid accidental zooming when scrolling over the map
          gestureHandling: true
        }).setView([52.1, 5.2], 7); // zoomlevel 7 works out nicely with a map of 500px high
        this.setLMap(mapObject);

        const osmUrl = 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';
        const osmAttrib = 'Map data © <a href="https://openstreetmap.org">OpenStreetMap</a> contributors';
        const osm = new L.TileLayer(osmUrl, {
          attribution: osmAttrib
        });
        this.getLMap().addLayer(osm);
        const features = this.parseLocationData();

        // after parsing the location data, create a custom control to filter on these items given theur type
        const typeFilterControl = this.createTypeFilterControl();
        this.getLMap().addControl(typeFilterControl);
        // now add the content based on the types available
        this.createTypeFilterControlContent(this.types);

        // add the custom control to zoom to the pointslayer:
        const zoomToAllControl = this.createZoomToAllControl();
        this.getLMap().addControl(zoomToAllControl);

        // on zoom:
        // after zooming, we need to explicitly set a few things on the icons created.
        // can only be done after zooming, because the icons/items on the map may change due to clustering
        this.getLMap().on("zoomend", function () {
          // wait a while, not nice, but we need the browser to be ready rendering the items (and updating the DOM). (Didn't find a nice event or hook instead, so use a timeout)
          setTimeout(_self.bindClusterIconEnter, 100);

          _self.bindFocusToIcons();
          // some accessibility issues need to be fixed for icons
          _self.fixAccessibilityIssues();
        })

        // we need to keep track of previously focused elements because of the spiderfy functionality in clusters
        _self.previousFocus = null;

        this.getLMap().on("popupopen", function (evt) {
          // find the first link in the contentNode, this is the header
          // or use the _closeButton:
          // jQuery(evt.popup._closeButton).focus()
          // jQuery(evt.popup._contentNode).find("a").focus();
          // focus on the content element
          jQuery(evt.popup._contentNode).find("div.leaflet-popup-content").focus();
          // TODO: if "esc" is chosen, close the popup?
          // "esc" is tuoghto use, maybe later implement this

        });
        this.getLMap().on("popupclose", function (evt) {
          try {
            _self.previousFocus.focus();
          } catch (e) {}
        })

      }
      this.enableClusters(this.clustering);
      this.fixAccessibilityIssues();
    }

    bindFocusToIcons() {
      // bind focus events on each marker/icon
      // for now for the spiderfying functions only
      const _self = this;
      jQuery(".leaflet-marker-icon").each(function (elem) {
        // first remove other focus events, to avoid stacking them
        jQuery(this).unbind("focus");
        jQuery(this).bind("focus", function (evt) {
          // keep track of the focussed element because of spiderfying and unspiderfying again
          _self.previousFocus = evt.currentTarget;
        })
      });
    }

    parseLocationData() {
      // to avoid scope issues, use _self
      const _self = this;
      const features = [];
      // reset the types
      const types = {};

      jQuery("." + this.mapItemClass).each(function (cntr, elem) {

        // for all elements with latitude and longitude data attributes, add a marker
        if (jQuery(elem).data("latitude") && jQuery(elem).data("longitude")) {
          const lat = jQuery(elem).data("latitude");
          const lon = jQuery(elem).data("longitude");

          const category = jQuery(elem).data("map-item-type") ? jQuery(elem).data("map-item-type") : "onbekend";
          var plaatsnaam = jQuery(elem).data("map-item-plaats") ? jQuery(elem).data("map-item-plaats") : "onbekende plaatsnaam";
          var initiatiefnaam = jQuery(elem).data("map-item-naam") ? jQuery(elem).data("map-item-naam") : "";

          // let's create a nice geojson feature for the data we found
          // use the list item content as popup content
          // also: keep track of the number of items for the type we found. This is for the Legend
          if (category) {
            if (types[category]) {
              types[category]["nrPosts"] += 1;
            } else {
              // dit is het eerste object in de verzameling. Dus we zetten de teller 'nrPosts'
              // op 1, niet op 0.
              types[category] = {
                "nrPosts": 1,
                "visible": true
              };
            }
          }
          // try to find a nice title
          let title = category;
          if (category in _self.typeLegendLabels) title = _self.typeLegendLabels[category];

          var label = '';
          if ( category && plaatsnaam && initiatiefnaam ) {
//            label = '[[ ' + initiatiefnaam + ' in ' + plaatsnaam + ']]';
            label = initiatiefnaam;
          }

          const feature = {
            "type": "Feature",
            "properties": {
              "category": category,
              "popupContent": elem.innerHTML,
              "ojectname": label,
            },
            "geometry": {
              "type": "Point",
              "coordinates": [lon, lat]
            }
          };
          features.push(feature);
        }
      });
      this.features = features;
      this.types = types;

      // recreate the Geojson layer
      const pointsLayer = this.createPointsLayer(features, _self)

      // Set initial zoom to the layer data
      const bounds = pointsLayer.getBounds();
      // add a margin around the bounds, to avoid points being too close on the edge of the map. Issue #27
      _self.getLMap().fitBounds(bounds, {
        padding: [50, 50]
      })
      this.pointsLayer = pointsLayer;
      // update the data, for later usage like removal of layers or whatever..
      this.features = features;
      return features;
    }

    createZoomToAllControl() {
      let _self = this;
      var ZoomAllControl = L.Control.extend({
        options: {
          position: 'topleft'
        },
        onAdd: function (map) {
          const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom');
          const a = L.DomUtil.create('a', 'leaflet-control-zoomall', container);
          a.innerHTML = '&harr;';
          a.href = '#';
          a.title = "Toon alles";
          a.onclick = function () {
            _self.getLMap().fitBounds(_self.pointsLayer.getBounds(), {
              padding: [50, 50]
            });
            return false;
          }
          return container;
        },
      });
      return new ZoomAllControl();
    }


    createPointsLayer(features, _self) {
      const layer = L.geoJSON(features, {
        pointToLayer: function (feature, latlng) {
          // wrapper function
          var category = feature.properties.category ? feature.properties.category : "onbekend";
          var labelTxt = _self.typeLegendLabels[category];
          if (feature.properties.ojectname) {
            labelTxt = feature.properties.ojectname;
          }
          if (_self.types[feature.properties.category]) {
            if (_self.types[feature.properties.category].visible == true) {
              // TODO: properly update the clustericons: only update counters, but keep same position
              // For now very hard, maybe next phase.
            } else {
              return false;
            }
          }
          // TODO: multiple categories for one initiatief? Multiple categories are postponed for now (decided in the group 10-12-2020)
          // current categories:
          // portaal, datalab, community, onbekend, strategie, visualisatie
          const customIcon = new _self.baseIcon({
            // customize according to category
            // PvB: ik heb de iconUrl aangepast en ervoor gezorgd dat er geen 404 meer
            // optreedt.
            // Thijs: de URL moet ook de basis bevatten (bij mij lokaal staat er nog /led/ voor). Nog een kleine aanpassing gedaan.
            iconUrl: _self.getIconURL(category),
          });
          let lbl = labelTxt;
          let marker = L.marker(latlng, {
            icon: customIcon,
            alt: labelTxt
          });
          return marker
        }
      }).bindPopup(function (layer) {
        return layer.feature.properties.popupContent;
      }).addTo(_self.getLMap());
      return layer;
    }

    getIconURL(category) {
      // Utils.siteurl is set in: public/class-initiatieven-kaart-public.php
      return `${Utils.siteurl}/wp-content/plugins/ictuwp-plugin-initiatievenkaart/public/css/images/marker-${category.toLowerCase()}.svg`;
    }

    createTypeFilterControl() {
      const _self = this;
      var TypeFilterControl = L.Control.extend({
        options: {
          position: 'topright'
        },
        onAdd: function (map) {
          const container = L.DomUtil.create('div', 'leaflet-bar leaflet-control leaflet-control-custom map-item-control-types');
          const clusterControlTxt = L.DomUtil.create('div', _self.typeFilterControlTxtId, container);
          clusterControlTxt.innerHTML = '';
          clusterControlTxt.id = _self.typeFilterControlTxtId;
          return container;
        },
      });

      const typeFilterControl = new TypeFilterControl();
      return typeFilterControl;
    }

    // content block for the control is created seperately, because of simpler code (don't use Leaflet domutils too much, jQuery is more commonly used)
    createTypeFilterControlContent() {
      const _self = this;

      // sort by keys, could be labels later
      const typeKeys = Object.keys(this.types);
      typeKeys.sort();

      let filterContent = jQuery(`<h3>`).html(`Toon initiatieven van:`);
      let filterContentList = jQuery(`<ul>`);
      for (var k in typeKeys) {
        const category = typeKeys[k];
        let labelTxt = category;
        if (category in this.typeLegendLabels) {
          labelTxt = this.typeLegendLabels[category];
        }
        const nrPosts = this.types[category].nrPosts;
        const inputId = `post-${category}`;
        const checkedTxt = (this.types[category].visible == false) ? "" : "checked";

        // create the checkbox for type selection
        let input = jQuery(`<input type="checkbox" id="${inputId}" ${checkedTxt}/>`);
        // note the scope _self: this function is only called from the GUI, so 'this' does not refer to this class. Use _self for that.
        jQuery(input).on('change', function (evt) {
          _self.toggleType(_self, category, evt.target.checked)
        });
        // create the icon of the type (issue #22)
        let iconTitle = `Icoon voor ${labelTxt}`;
        let iconImg = jQuery(`<img>`).attr('src', this.getIconURL(category)).attr('title', iconTitle).attr('alt', iconTitle).attr('aria-hidden', 'true');
        let labelElem = jQuery(`<label for="${inputId}">${labelTxt} (${nrPosts})</label>`);

        // now glue it together for the list
        let li = jQuery(`<li>`).append(input).append(iconImg).append(labelElem);
        filterContentList.append(li);
      }
      // filterContent.append(filterContentList);
      jQuery("#" + this.typeFilterControlTxtId).html(filterContent).append(filterContentList);
      return filterContent;
    }

    toggleType(_self, category, show) {
      var visible = false;
      if (show == undefined) {
        visible = false;
      }
      if (show) {
        visible = true;
      }
      _self.types[category]["visible"] = visible;
      // recreate layer
      _self.recreatePointsLayer()
    }

    recreatePointsLayer() {
      this.pointsLayer.remove();
      this.getLMap().removeLayer(this.clusterLayer);
      this.pointsLayer = this.createPointsLayer(this.features, this);
      // enable clustering again? use setting for this?
      this.enableClusters(this.clustering);
      // fix accessibility issues Leaflet: quite ugly this way, but faster than in Leaflet itself. If fixes are adequate try to port it to Leaflet core
      this.fixAccessibilityIssues();
    }


    fixAccessibilityIssues() {
      // tabindex, labels, roles for elements where this apparerently can;t be done at initialization
      jQuery(".leaflet-marker-icon:not('.clusterIcon')").attr("role", "button").attr("tabindex", "0");
      // clustericon: multiple
      jQuery(".clusterIcon").attr("role", "button").attr("tabindex", "0");
      // custom controls:
      jQuery(".leaflet-control-zoomall").attr("role", "button").attr("aria-label", "Toon alles").attr("tabindex", "0");

      // shadows: explicit hide these
      jQuery(".leaflet-marker-shadow").attr("aria-hidden", "true");

      jQuery(".leaflet-overlay-pane svg").attr("role", "presentation").attr("aria-label", "Kaart met initiatieven");

      // no tabindex: the links inside should be accessible only
      jQuery(".leaflet-control-attribution").attr("tabindex", "0").attr("aria-label", "Attribution");
    }

    toggleListMap() {
      if (jQuery("#" + this.mapElementId).is(":visible")) {
        // hide the map, show the list
        jQuery("#" + this.mapElementId).hide();
        jQuery("#" + this.mapItemsContainerId).show();
        jQuery("#toggleListMapButton").html("Toon de kaart");
      } else {
        // the other way around
        jQuery("#" + this.mapElementId).show();
        jQuery("#" + this.mapItemsContainerId).hide();
        jQuery("#toggleListMapButton").html("Toon de lijst");
      }
      this.getLMap().invalidateSize();
      return true;
    }

    enableClusters(enable) {
      // Configuration of clustering via init function
      const _self = this;
      this.clustering = enable;
      if (enable) {
        const clusterLayer = L.markerClusterGroup({
          maxClusterRadius: 32,
          showCoverageOnHover: false,
          iconCreateFunction: function (cluster) {
            var sizeClass = 'sm';
            const baseSize = 20;
            const increaseSize = 6;
            var iconSize = L.point(baseSize, baseSize);

            if (cluster.getChildCount() >= 10) {
              sizeClass = 'md';
              iconSize = L.point(baseSize + increaseSize, baseSize + increaseSize)
            } else if (cluster.getChildCount() >= 100) {
              sizeClass = 'lg';
              iconSize = L.point(baseSize + 2 * increaseSize, baseSize + 2 * increaseSize);
            }
            return L.divIcon({
              html: `<span aria-label="${cluster.getChildCount()} initiatieven">${cluster.getChildCount()}</span>`,
              className: 'clusterIcon clusterIcon-' + sizeClass,
              iconSize: iconSize
            });
          }
        });

        clusterLayer.on('click', function (evt) {
          // an individual marker is clicked, use evt.layer for that
          // _self.previousOpened = evt.layer;
        });

        // for keyboardsupport add some functions to cluster events.
        // and make sure (some) accessibility issues in the content after interaction are fixed
        _self.unspiderfied = false;
        clusterLayer.on('clusterclick', function (evt) {
          // make sure that another enter or click closes the clustericon again
          if (_self.unspiderfied == false) {
            // close cluster
            evt.layer.unspiderfy();
            _self.fixAccessibilityIssues();
          }
        });

        clusterLayer.on('unspiderfied', function (evt, markers) {
          // set focus back tot the icon of the marker
          // need to use internal references, becauase there is no proper getter in Leaflet to get the icon element
          evt.cluster._icon.focus();
          _self.unspiderfied = true;
          _self.fixAccessibilityIssues();
        });

        clusterLayer.on('spiderfied', function (evt) {
          // get the icons and focus on one of them
          // overrule the hardcoded opacity of .3 in leaflet.markercluster.js
          // can't be done with CSS unfortunately
          // add the focus elements:
          _self.bindFocusToIcons();
          evt.cluster.setOpacity(_self.iconSpiderfiedOpacity)
          if (evt.markers) {
            if (evt.markers.length > 0) {
              evt.markers[0]._icon.focus();
            }
          }
          _self.unspiderfied = false;
          _self.fixAccessibilityIssues();
        });

        // for this map it seems to save to assume that all point layers should be in the clusterlayer
        _self.unclusteredLayers = [];
        // this will keep track of all the subgroup arrays
        _self.subgroups = {}
        _self.getLMap().eachLayer(function (layer) {
          _self.unclusteredLayers.push(layer);
          if (layer.feature) {
            if (layer.feature.geometry.type === "Point") {
              clusterLayer.addLayer(layer);
              _self.getLMap().removeLayer(layer);
            }
          }
        });
        // after forming the groups, add them
        // mySubGroup = L.featureGroup.subGroup(clusterLayer, arrayOfMarkers);

        _self.getLMap().addLayer(clusterLayer);
        // keep track of the clusterLayer too, to make it removable
        _self.clusterLayer = clusterLayer;

      } else {
        if (_self.unclusteredLayers.length > 0) {
          // remove the clusterlayer
          for (const l in _self.unclusteredLayers) {
            _self.getLMap().addLayer(_self.unclusteredLayers[l]);
          }
        }
        _self.getLMap().removeLayer(_self.clusterLayer);
        _self.clusterLayer = {};
      }

      this.bindFocusToIcons();
      this.bindClusterIconEnter();

    }

    // on enter: open/close the cluster
    bindClusterIconEnter() {
      // add focusable elements around the markers. Do this for each clusterIcon, also after zoom
      let eventedIcons = 0;
      // create a list of icons in the map view that should only be accessible by TAB?

      jQuery(".clusterIcon").each(function (elem) {
        eventedIcons++;
        jQuery(this).keypress(function (event) {
          var keycode = (event.keyCode ? event.keyCode : event.which);
          // on enter or spacebar:
          // TODO: spacebar is nasty, interferes with default browser behaviour
          // Use ENTER only for now
          // 13 = enter, 32 = spacebar
          // if(keycode == '13' || keycode == '32')
          if (keycode == '13') {
            // zoom to clustericon
            // trigger a click on enter ...
            jQuery(this).click();
            event.preventDefault();
            return false;
          }
          event.stopPropagation();
        })
      });
    }

  }

  /* -----------------------------------
  Configure and init the map after the page is ready
  ----------------------------------- */
  jQuery(function () {

    const fullConfig = {
      // mandatory: the id of the HTML list that contains the map items
      "mapItemsContainerId": "map-items",
      // mandatory:  the class for an item in the list that needs to be displayed on the map
      "mapItemClass": "map-item",
      // optional: other map id for styling for example
      "mapElementId": "initiatieven-kaart-map",
      // optional: the CSS class for the HTML element where the map is created in. If changed, make sure there is a style rule that specifies the height of the HTML element, because otherwise LeafletJS can't generate a map
      "mapClass": "archives-map",
      // Clustering creates icons with counters for items that are too close to each other for a good visualisation. On clicking a cluster it zooms to the items of the cluster or "spiderfies" them to show the individual items.
      "clustering": true,
      // the Leaflet plugin for clusters requires to set the opacity of a an icon through JavaScript
      "iconSpiderfiedOpacity": 0.75,
      // Optional: labels in the legend for each type. The property name (e.g. "portaal") should be the same as the type code of the item, as defined in the Wordpress taxonomy
      "typeLegendLabels": {
        "onbekend": "Onbekend",
        "community": "Community",
        "datalab": "Datalab", // datalab is the old type, because in test/older data this can be present, just put it here
        "lab": "Datalab",
        "portaal": "Portaal",
        "strategie": "Strategie",
        "visualisatie": "Visualisatie",
      },
      // Optional, icon size (width x height): 24 x 32
      // best is to make nice numbers using the ratio 3:4, given the SVG icons provided
      "iconWidth": 24,
      "iconHeight": 32
    }

    /* the minimal config is:

		const minConfig = {
			"mapItemsContainerId": "map-items",
	    "mapItemClass": "map-item",
		}

    */
    const itemsMap = new HTMLItemsMap(fullConfig);
    itemsMap.initMap();

  });

})(jQuery);

