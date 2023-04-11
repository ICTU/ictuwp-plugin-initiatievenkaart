# Initiatievenkaart

WordPress-plugin voor het op een kaart tonen van organisaties en initiatieven om datagedreven te werken.
Wordt (later) gebruikt op [www.digitaleoverheid.nl/initiatief/](https://www.digitaleoverheid.nl/initiatief/)

## Afhankelijkheden
Deze plugin functioneert niet zonder:
* [Advanced Custom Fields (PRO)](https://www.advancedcustomfields.com/pro/) (plugin)
* [ACF OpenStreetMap Field](https://wordpress.org/plugins/acf-openstreetmap-field/) (plugin)
* [Leaflet JS](https://leafletjs.com/) en enkele plugins:
  - [MarkerCluster](https://github.com/Leaflet/Leaflet.markercluster)
  - [Gesture handling](https://github.com/elmarquis/Leaflet.GestureHandling/)

## Contact
Code is geschreven door:
* Thijs Brentjens (thijs@brentjensgeoict.nl)
* Paul van Buuren (paul@wbvb.nl)

In opdracht van het Leer- en Expertisepunt Datagedreven Werken (LED) van ICTU, Ministerie van Binnenlandse Zaken en Koninkrijksrelaties (BZK).

## Toegankelijkheid
De Initiatievenkaart probeert aan de Toegankelijkheidseisen te voldoen door:
1. een tekstuele representatie (HTML lijst met data-attributen per item: `data-latitude`, `data-longitude` en `data-map-item-type` als categorie/type item) te gebruiken van de gegevens die op de kaart moeten komen. Via Javascript worden deze gegevens uitgelezen en op de kaart gepresenteerd. De HTML list item content is de popup content
2. gebruik van iconen op de kaart om onderscheid te maken tussen type initiatieven (niet alleen kleur). Er zijn SVG iconen beschikbaar voor elk type initiatief
3. toetsenbordtoegankelijkheid kaart: de kaart bedienen via het toetsenbord, door met TAB door de items op de kaart te bladeren, popups te openen/sluiten (ENTER) en de tools voor in/uitzoomen te kunnen bedienen.
4. semantiek: ARIA labels, titles toegevoegd.
   1. Dit soort zaken wordt niet makkelijk en netjes in LeafletJS afgehandeld voor alle elementen. Voor nu een functie gemaakt die "achteraf" enkele zaken toevoegt / corrigeert. Zie de Javascript code `public/js/initiatieven-kaart-public.js` en dan de functie `fixAccessibilityIssues()`
5. focus styles voor iconen en popup sluit buttons

Weet je dingen die beter kunnen? Maak vooral een [Github issue](../../issues/new) aan.

### Toetsenbordtoegankelijkheid iconen op de kaart
Focus wordt nu als volgt gezet:
Enkelvoudige / ongeclusterde iconen:
- na openen popup op de header link (link naar initiatief). NB: dit was oorspronkelijk de sluit button rechts boven
- na sluiten popup: focus terug naar de marker waarvan de popup geopend was

Cluster iconen:
- bij openen (enter / klikken):
  - zoom naar de initiatieven als het een cluster is waarvan de initiatieven dichtbij elkaar liggen, maar niet op dezelfde plek. _of_
  - klap het cluster uit ("spiderfy") en toon de individuele iconen die het cluster vormen, als de initiatieven wel dezelfde locatie hebben. Focus ligt dan op een icoon van dit cluster.
- via TAB / shift+TAB terug te gaan naar het clustericoon. Als daarop enter wordt gedrukt (idem als een "klik"), dan komt de focus terug op het clustericoon

## Styling: CSS en SVG
Er is zo min mogelijk specifieke CSS in gebruik voor de kaart. Er zijn een paar classes die nuttig kunnen zijn aan te passen:
1. `clusterIcon` classes
2. de hoogte van de kaart (class: `archives-map`) is standaard 500px. Leaflet heeft een opgegeven hoogte nodig. Bij 500px hoogte wordt Nederland volledig bij gebruik van WebMercator achtergrondkaarten (zoals OpenStreetMap)
3. de focus styles: `.leaflet-marker-icon:focus, a.leaflet-popup-close-button:focus`

### SVG Iconen op de kaart
SVG iconen voor de kaart, geplaatst in de directory `css/images/`. Gebruik het type initiatief in de bestandsnaam, volgens:
`marker-{type initiatief}.svg`. Bijvoorbeeld: `marker-visualisatie.svg` voor een icoon dat als map-item-type / type initiatief de code `visualisatie` heeft.

## Suggesties, openstaande punten en doorontwikkeling
Zie de [Github issues](../../issues) voor openstaande punten. Voor issues en suggesties voor verbetering kan je ook daar terecht. Er is op dit moment geen actief en structureel beheer op deze plugin geregeld/voorzien. Dus we geven geen garanties voor het oplossen.

De code is beschikbaar onder een open source licentie. Voel je vrij een Pull request te doen als je concrete verbeteringen hebt.

## Configuratie van de kaart
Zie `js/initiatieven-kaart-public.js` voor de volledige code, onderaan het bestand. De configuratie is nodig om de HTML lijst en de te gebruiken items te identificeren. Hiermee wordt de kaart opgebouwd.

Configuratie gaat via een JSON object. Zie hieronder in de voorbeelden per optie beknopt wat een optie doet.

Configuratie voorbeelden:

```
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
  "iconSpiderfiedOpacity" : 0.75,
  // Optional: labels in the legend for each type. The property name (e.g. "portaal") should be the same as the type code of the item, as defined in the Wordpress taxonomy
  "typeLegendLabels" : {
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

const minConfig = {
 	"mapItemsContainerId": "map-items",
  "mapItemClass": "map-item",
}

```

Met deze configuratie, maak een kaart aan en initialiseer alles, zodat data uit de HTML lijst wordt gelezen en een kaart met popups wordt gegenereerd:

```
const itemsMap = new HTMLItemsMap(fullConfig);
itemsMap.initMap();
```

## De initiatievenlijst tonen via een pagina
Je kunt de initiatievenlijst ook tonen via een pagina. Dat is het posttype 'pagina' dat standaard in WordPress beschikbaar is. Maak een pagina aan via:

[admin] > Pagina's > Nieuwe pagina

In de pagina-instellingen moet je als template kiezen: 'initiatievenpagina'.

### Wijs de pagina aan als initiatievenoverzicht via de Customizer
De standaard URL-opbouw voor een initiatief is als volgt:

`[jouwdomein.tld]/initiatief/slug-van-een-initiatief/`

Als je een eigen pagina hebt waarop je de initiatieven toont, kan het handig zijn om de URL pagina aan te wijzen als de basis voor initiatieven. Stel dat jouw pagina deze URL heeft:

`[jouwdomein.tld]/eerste-pagina/tweede-pagina/pagina-met-alle-initiatieven/`

dan kunnen we daar dit van maken:

`[jouwdomein.tld]/eerste-pagina/tweede-pagina/pagina-met-alle-initiatieven/slug-van-een-initiatief/`

Dit doe je via de Customizer, als je admin-rechten hebt op je website, via:

[admin] > Weergave > Customizer > Initiatievenkaart

Kies uit de dropdown-lijst de pagina waarop alle initatieven staan.

## Current version:
* 1.0.11 - PHP 8.x compatibility check. 
