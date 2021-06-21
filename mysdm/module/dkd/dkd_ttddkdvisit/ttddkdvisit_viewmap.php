<!DOCTYPE html>
<html>
  <head>
    <title>Street View</title>
    <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
    <style>
        html,
        body {
          height: 100%;
          margin: 0;
          padding: 0;
        }

        #map,
        #pano {
          float: left;
          height: 100%;
          width: 50%;
        }
    </style>
    <script>
        function initialize() {
          const fenway = { lat: -6.1912223, lng: 106.8503058 };
          const map = new google.maps.Map(document.getElementById("map"), {
            center: fenway,
            zoom: 14,
          });
          const panorama = new google.maps.StreetViewPanorama(
            document.getElementById("pano"),
            {
              position: fenway,
              pov: {
                heading: 34,
                pitch: 10,
              },
            }
          );
          map.setStreetView(panorama);
        }

    </script>
  </head>
  <body>
    <div id="map"></div>
    <div id="pano"></div>

    <!-- Async script executes immediately and must be after any DOM elements used in callback. -->
    <script
      src="https://maps.googleapis.com/maps/api/js?key=AIzaSyDSh9vsayfJZYKmol6P3HDeRgjAInzUjjk&callback=initialize&libraries=&v=weekly"
      async
    ></script>
  </body>
</html>