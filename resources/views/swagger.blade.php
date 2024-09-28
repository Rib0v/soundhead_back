<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
  <head>
    <meta charset="UTF-8">
    <title>Swagger UI</title>
    <link rel="stylesheet" type="text/css" href="{{ url(config('swagger.path') . '/swagger-ui.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ url(config('swagger.path') . '/index.css') }}" />
    <link rel="icon" type="image/png" href="{{ url(config('swagger.path') . '/favicon-32x32.png') }}" sizes="32x32" />
    <link rel="icon" type="image/png" href="{{ url(config('swagger.path') . '/favicon-16x16.png') }}" sizes="16x16" />
  </head>

  <body>
    <div id="swagger-ui"></div>
    <script src="{{ url(config('swagger.path') . '/swagger-ui-bundle.js') }}" charset="UTF-8"> </script>
    <script src="{{ url(config('swagger.path') . '/swagger-ui-standalone-preset.js') }}" charset="UTF-8"> </script>
    <script>
        window.onload = function() {
            window.ui = SwaggerUIBundle({
                url: "{{ url(config('swagger.yaml')) }}",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout"
            });
        };
    </script>
  </body>
</html>