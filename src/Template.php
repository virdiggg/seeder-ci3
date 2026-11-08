<?php

namespace Virdiggg\SeederCi3;

/**
 * Template
 *
 * A small Blade-like layout engine for CodeIgniter 3, built on output
 * buffering. A child view declares its layout with extend(), fills named
 * slots with section()/end(), and the layout prints them with yield_section().
 * A view that never calls extend() is rendered as-is (a plain full page).
 *
 * Not loaded through CI's loader: use Template::instance(), or let
 * MY_Controller::asTemplate() do it for you.
 */
class Template
{
    /**
     * Shared instance, so views and helpers reach the same state as the controller.
     *
     * @var Template|null
     */
    protected static $instance = null;

    /**
     * CodeIgniter super object.
     *
     * @var \CI_Controller
     */
    protected $CI;

    /**
     * Layout requested by the view that was just rendered, or null.
     *
     * @var string|null
     */
    protected $layout = null;

    /**
     * Captured section contents, keyed by section name.
     *
     * @var array<string, string>
     */
    protected $sections = [];

    /**
     * Captured push contents, keyed by stack name (one chunk per push() call).
     *
     * @var array<string, string[]>
     */
    protected $pushes = [];

    /**
     * Currently open buffers, innermost last: [name, 'section'|'push'].
     *
     * @var array<int, array{0: string, 1: string}>
     */
    protected $stack = [];

    public function __construct()
    {
        $this->CI =& get_instance();
    }

    /**
     * Get the shared instance, creating it on first use.
     */
    public static function instance(): Template
    {
        if (self::$instance === null) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * Render a view inside its declared layout(s).
     *
     * @param string               $view   View path, e.g. 'admin/dashboard'
     * @param array<string, mixed> $data   Variables passed to every view in the chain
     * @param bool                 $return TRUE to return the HTML instead of sending it to the output
     * @return string|void         The final HTML when $return is TRUE
     */
    public function render(string $view, array $data = [], bool $return = false)
    {
        $this->layout   = null;
        $this->sections = [];
        $this->pushes   = [];
        $this->stack    = [];

        // Render the child first: it may call extend() and define sections.
        $html = $this->CI->load->view($view, $data, true);

        // Follow the layout chain (a layout may itself extend another layout).
        while ($this->layout !== null) {
            $layout       = $this->layout;
            $this->layout = null;
            $html         = $this->CI->load->view($layout, $data, true);
        }

        if ($return) {
            return $html;
        }

        $this->CI->output->append_output($html);
    }

    /**
     * Declare which layout the current view extends.
     */
    public function extend(string $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Define a section. Pass $content for a one-liner, or omit it to open a
     * buffer that must be closed with end().
     *
     * @param string      $name
     * @param string|null $content Inline content, or null to start buffering
     * @return void
     */
    public function section(string $name, $content = null)
    {
        if ($content !== null) {
            $this->sections[$name] = $content;
            return;
        }

        $this->stack[] = [$name, 'section'];
        ob_start();
    }

    /**
     * Start appending to a named stack (like Blade's @push). Close with end().
     */
    public function push(string $name)
    {
        $this->stack[] = [$name, 'push'];
        ob_start();
    }

    /**
     * Close the innermost open section() or push() buffer.
     *
     * @throws \LogicException When nothing is open
     */
    public function end()
    {
        if (empty($this->stack)) {
            throw new \LogicException('Template::end() called without an open section() or push().');
        }

        list($name, $type) = array_pop($this->stack);
        $buffer        = (string) ob_get_clean();

        if ($type === 'push') {
            $this->pushes[$name][] = $buffer;
        } else {
            $this->sections[$name] = $buffer;
        }
    }

    /**
     * Get a section's content (like Blade's @yield).
     */
    public function yield_section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    /**
     * Get everything pushed to a named stack (like Blade's @stack).
     */
    public function stack(string $name): string
    {
        return isset($this->pushes[$name]) ? implode('', $this->pushes[$name]) : '';
    }
}