<?php

namespace Virdiggg\SeederCi3\Exporters;

/**
 * Renders the Swagger UI page as a CodeIgniter view (application/views/swagger.php).
 *
 * The view expects the app's helpers: assets() for the vendored Swagger UI
 * files and base_url(). Controller side:
 *
 *   $this->load->view('swagger', ['swaggerFile' => base_url('swagger-api.json')]);
 *
 * $swaggerFile is optional and defaults to base_url('swagger-api.json').
 *
 * The API server URL is not baked in. If the spec has no "servers", the page
 * uses base_url() of the request that rendered it, which already accounts for
 * the host, the published port and a sub folder. Add ?server=https://host/app
 * to the page URL to override it.
 */
class SwaggerUiExporter
{
    /** Folder under assets/vendor holding swagger-ui-bundle.js and swagger-ui.css. */
    const UI_VERSION = '5.35.0';

    /**
     * @param string $title Page title
     *
     * @return string PHP source of the view
     */
    public function render($title = 'API Docs')
    {
        $cdn = 'https://cdn.jsdelivr.net/npm/swagger-ui-dist@' . self::UI_VERSION;
        $css = $cdn . '/swagger-ui.css';
        $js = $cdn . '/swagger-ui-bundle.min.js';

        return strtr(self::TEMPLATE, [
            '@@TITLE@@' => htmlspecialchars((string) $title, ENT_QUOTES, 'UTF-8'),
            '@@CSS@@' => $css,
            '@@JS@@' => $js,
        ]);
    }

    const TEMPLATE = <<<'PHP'
<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@@TITLE@@</title>
  <link rel="stylesheet" href="@@CSS@@">
</head>
<body>
  <div id="swagger-ui"></div>
  <script src="@@JS@@"></script>
  <script>
    const SPEC_FILE  = <?= json_encode(isset($swaggerFile) ? $swaggerFile : base_url('swagger-api.json'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;
    const SERVER_URL = <?= json_encode(rtrim(base_url(), '/'), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) ?>;

    window.onload = function () {
      const target = document.getElementById('swagger-ui');

      fetch(SPEC_FILE, { cache: 'no-store' })
        .then(function (response) {
          if (!response.ok) {
            throw new Error('HTTP ' + response.status);
          }
          return response.json();
        })
        .then(function (spec) {
          const override = new URLSearchParams(window.location.search).get('server');

          if (override) {
            spec.servers = [{ url: override.replace(/\/+$/, '') }];
          } else if (!spec.servers || spec.servers.length === 0) {
            spec.servers = [{ url: SERVER_URL || window.location.origin }];
          }

          window.ui = SwaggerUIBundle({
            spec: spec,
            dom_id: '#swagger-ui',
            deepLinking: true
          });
        })
        .catch(function (error) {
          target.textContent = 'Could not load ' + SPEC_FILE + ': ' + error.message;
        });
    };
  </script>
</body>
</html>

PHP;
}