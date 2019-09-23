<?php
/**
 * WP File Download
 *
 * @package WP File Download
 * @author  Joomunited
 * @version 1.0.3
 */

//-- No direct access
defined('ABSPATH') || die();

/**
 * Class WpfdThemeUcftable
 */
class WpfdThemeUcftable extends WpfdTheme
{
    /**
     * Theme name
     *
     * @var string
     */
	public $name = 'ucftable';

    /**
     * Get tpl path for include
     *
     * @return string
     */
    public function getTplPath()
    {
        return dirname(__FILE__) . DIRECTORY_SEPARATOR . 'tpl.php';
    }

    /**
     * Load template hooks
     *
     * @return void
     */
    public function loadHooks()
    {
        $this->hideEmpty(false);
        parent::loadHooks();
        $this->customAssets();
    }

    /**
     * Load custom hooks and filters
     *
     * @return void
     */
    public function loadCustomHooks()
    {
        $name = $this->getThemeName();

        add_filter('wpfd_' . $name . '_content_wrapper', array(__CLASS__, 'contentWrapper'), 10, 2);

		// Remove actions
		remove_action( 'wpfd_' . $name . '_before_files_loop_handlebars', array( 'WpfdTheme', 'showCategoryTitleHandlebars'  ), 20 );
		remove_action( 'wpfd_' . $name . '_before_files_loop', array( 'WpfdTheme', 'showCategoryTitle' ), 20 );

		if ( (int) WpfdBase::loadValue( $this->params, self::$prefix . 'showcategorytitle', 1 ) === 1 ) {
			add_action( 'wpfd_' . $name . '_before_files_loop_handlebars', array( __CLASS__, 'showCategoryTitleHandlebars' ), 20, 2 );
			add_action( 'wpfd_' . $name . '_before_files_loop', array( __CLASS__, 'showCategoryTitle' ), 20, 2 );
		}

		remove_action( 'wpfd_' . $name . '_before_files_loop_handlebars', array( 'WpfdTheme', 'showCategoriesHandlebars' ), 30 );
		remove_action( 'wpfd_' . $name . '_before_files_loop', array( 'WpfdTheme', 'showCategories' ), 30 );

		if ( (int) WpfdBase::loadValue( $this->params, self::$prefix . 'showsubcategories', 1 ) === 1 ) {
			add_action( 'wpfd_' . $name . '_before_files_loop_handlebars', array( __CLASS__, 'showCategoriesHandlebars' ), 30, 2 );
			add_action( 'wpfd_' . $name . '_before_files_loop', array( __CLASS__, 'showCategories' ), 30, 2 );
		}

        // Using local title
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showtitle', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thTitle'), 10);
            add_action('wpfd_' . $name . '_file_info_handlebars', array(__CLASS__, 'showTitleHandlebars'), 5, 2);
            add_action('wpfd_' . $name . '_file_info', array(__CLASS__, 'showTitle'), 5, 3);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdescription', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thDesc'), 20);
            add_filter('wpfd_' . $name . '_file_info_description_handlebars_args', array(
                __CLASS__,
                'descriptionHandlebars'
            ), 10, 3);
            add_filter('wpfd_' . $name . '_file_info_description_args', array(__CLASS__, 'description'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showversion', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thVersion'), 30);
            add_filter('wpfd_' . $name . '_file_info_version_handlebars_args', array(__CLASS__, 'versionHandlebars'), 10, 3);
			add_filter('wpfd_' . $name . '_file_info_version_args', array(__CLASS__, 'version'), 10, 4);
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showsize', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thSize'), 70);
            add_filter('wpfd_' . $name . '_file_info_size_handlebars_args', array(__CLASS__, 'sizeHandlebars'), 10, 3);
			add_filter('wpfd_' . $name . '_file_info_size_args', array(__CLASS__, 'size'), 10, 4);

            // Reprioritize
            remove_action( 'wpfd_' . $name . '_file_info_handlebars', array('WpfdTheme', 'showSizeHandlebars'), 30 );
			add_action( 'wpfd_' . $name . '_file_info_handlebars', array('WpfdTheme', 'showSizeHandlebars'), 60, 3 );
			remove_action( 'wpfd_' . $name . '_file_info', array(__CLASS__, 'showSize'), 30 );
			add_action( 'wpfd_' . $name . '_file_info', array(__CLASS__, 'showSize'), 60, 3 );
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showhits', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thHits'), 40);
            add_filter('wpfd_' . $name . '_file_info_hits_handlebars_args', array(__CLASS__, 'hitsHandlebars'), 10, 3);
			add_filter('wpfd_' . $name . '_file_info_hits_args', array(__CLASS__, 'hits'), 10, 4);

            remove_action( 'wpfd_' . $name . '_file_info_handlebars', array( 'WpfdTheme', 'showHitsHandlebars' ), 40 );
            add_action( 'wpfd_' . $name . '_file_info_handlebars', array( 'WpfdTheme', 'showHitsHandlebars' ), 30, 2 );
			remove_action( 'wpfd_' . $name . '_file_info', array( __CLASS__, 'showHits' ), 40 );
			add_action( 'wpfd_' . $name . '_file_info', array( __CLASS__, 'showHits' ), 30, 3 );
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdateadd', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thCreated'), 50);
            add_filter('wpfd_' . $name . '_file_info_created_handlebars_args', array(__CLASS__, 'createdHandlebars'), 10, 3);
			add_filter('wpfd_' . $name . '_file_info_created_args', array(__CLASS__, 'created'), 10, 4);

            remove_action( 'wpfd_' . $name . '_file_info_handlebars', array( 'WpfdTheme', 'showCreatedHandlebars' ), 50 );
			add_action( 'wpfd_' . $name . '_file_info_handlebars', array( 'WpfdTheme', 'showCreatedHandlebars' ), 40, 2 );
			remove_action( 'wpfd_' . $name . '_file_info', array( __CLASS__, 'showCreated' ), 50 );
			add_action( 'wpfd_' . $name . '_file_info', array( __CLASS__, 'showCreated' ), 40, 3 );
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdatemodified', 0) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thModified'), 60);
            add_filter('wpfd_' . $name . '_file_info_modified_handlebars_args', array(__CLASS__, 'modifiedHandlebars'), 10, 3);
			add_filter('wpfd_' . $name . '_file_info_modified_args', array(__CLASS__, 'modified'), 10, 4);

            remove_action( 'wpfd_' . $name . '_file_info_handlebars', array( __CLASS__, 'showModifiedHandlebars' ), 60 );
			add_action( 'wpfd_' . $name . '_file_info_handlebars', array( __CLASS__, 'showModifiedHandlebars' ), 50, 3 );
			remove_action( 'wpfd_' . $name . '_file_info', array( __CLASS__, 'showModified' ), 60 );
			add_action( 'wpfd_' . $name . '_file_info', array( __CLASS__, 'showModified' ), 50, 3 );
        }
        if ((int) WpfdBase::loadValue($this->params, self::$prefix . 'showdownload', 1) === 1) {
            add_action('wpfd_' . $name . '_columns', array(__CLASS__, 'thDownload'), 80);
        }
    }

    /**
     * Load custom assets
     *
     * @return void
     */
    public function customAssets()
    {
        $this->additionalClass = '';

        if (WpfdBase::loadValue($this->params, self::$prefix . 'styling', true)) {
            $this->additionalClass .= 'table ';
        }

        // Load additional scripts

        if (WpfdBase::checkExistTheme($this->name)) {
            $url = plugin_dir_url($this->path . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR . 'wpfd-' . $this->name . DIRECTORY_SEPARATOR . 'foobar');
        } else {
            $url  = wpfd_abs_path_to_url(realpath(dirname(wpfd_locate_theme($this->name, 'theme.php'))) . DIRECTORY_SEPARATOR);
		}

		wp_dequeue_style( 'wpfd-theme-' . $this->name );
		wp_dequeue_script( 'wpfd-theme-' . $this->name );
		wp_deregister_style( 'wpfd-theme-' . $this->name );
		wp_deregister_script( 'wpfd-theme-' . $this->name );

		wp_enqueue_style( 'wpfd-theme-' . $this->name, $url . 'dist/css/style.min.css', array(), WPFD_VERSION );
		wp_enqueue_script( 'wpfd-theme-' . $this->name, $url . 'dist/js/script.min.js', array(), WPFD_VERSION );

		wp_localize_script(
            'wpfd-theme-ucftable',
            'wpfdparams',
            array('wpfdajaxurl' => $this->ajaxUrl, 'columns' => esc_html__('Columns', 'wpfd'))
        );
	}

	/**
	 * Print category heading for handlebars template
	 * @param array $config The config array
	 * @param array $params The parameter array
	 */
	public static function showCategoryTitleHandlebars( $config, $params ) {
		ob_start();
?>
		{{#if category}}{{#with category}}
		<h2 class="heading-underline mb-4">{{name}}</h2>
		{{#if parent}}
		<a class="catlink wpfdcategory backcategory" href="#" data-idcat="{{parent}}">
			<i class="zmdi zmdi-chevron-left"></i> Back
		</a>
		{{/if}}
		{{/with}}{{/if}}
<?php
		echo trim( ob_get_clean() );
	}

	/**
	 * Print category heading for the initial template
	 * @param array $config The config array
	 * @param array $params The parameter array
	 */
	public static function showCategoryTitle( $config, $params ) {
		ob_start();
		if ( isset( $config->options['category'] ) ) : $category = $config->options['category'];
?>
		<h2 class="heading-underline mb-4"><?php echo $category->name; ?></h2>
		<?php if ( $category->parent ) : ?>
		<a class="catlink wpfdcategory backcategory" href="#" data-idcat="<?php echo $category['parent']; ?>">
            <i class="zmdi zmdi-chevron-left"></i> Back
        </a>
		<?php endif; ?>
<?php
		endif;
		echo trim( ob_get_clean() );
	}

	/**
	 * Categories template for handlebars
	 * @param array $config The config array
	 * @param array $params The params array
	 */
	public static function showCategoriesHandlebars( $config, $params ) {
		ob_start();
?>
		{{#if categories}}
		<table class="table">
			<thead>
				<tr class="row align-items-center">
					<th class="col-8">Directory</th>
					<th class="col text-right">File Count</th>
				</tr>
			</thead>
			<tbody>
				{{#each categories}}
				<tr class="row d-flex align-items-center">
					<td class="col-8">
						<a class="catlink d-flex align-items-center" href="#" data-idcat="{{term_id}}" title="{{name}}">
							<span class="fa fa-folder fa-2x mr-2"></span><span>{{name}}</span>
						</a>
					</td>
					<td class="col text-right">
						{{count}}
					</td>
				</tr>
				{{/each}}
			</tbody>
		</table>
		{{/if}}
<?php
		echo trim( ob_get_clean() );
	}

	/**
	 * Categories template for static
	 * @param array $config The config array
	 * @param array $params The params array
	 */
	public static function showCategories( $config, $params ) {
		ob_start();
		if ( isset( $config->options['categories'] ) ) :
?>
		<table class="table">
			<thead>
				<tr class="row align-items-center">
					<th class="col-8">Directory</th>
					<th class="col text-right">File Count</th>
				</tr>
			</thead>
			<tbody>
			<?php foreach( $config->options['categories'] as $cat ) : ?>
				<tr class="row d-flex align-items-center">
					<td class="col-8">
						<a class="catlink d-flex align-items-center" href="#" data-idcat="<?php echo $cat->term_id; ?>" title="<?php echo $cat->name; ?>">
							<span class="fa fa-folder fa-2x mr-2"></span><span><?php echo $cat->name; ?></span>
						</a>
					</td>
					<td class="col text-right">
						<?php echo $cat->count; ?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
<?php
		endif;
		echo trim( ob_get_clean() );
	}

    /**
     * Print content wrapper
     *
     * @param string $wrapper Content wrapper html
     * @param object $theme   Current theme object
     *
     * @return string
     */
    public static function contentWrapper($wrapper, $theme)
    {
        $wpfdcontentclass = '';
        if (WpfdBase::loadValue($theme->params, self::$prefix . 'stylingmenu', true)) {
            $wpfdcontentclass .= 'colstyle';
        }

        return sprintf(
            '<div class="wpfd-content wpfd-content-' . $theme->name . ' wpfd-content-multi %s" data-category="%s">',
            (string) esc_attr($wpfdcontentclass),
            (string) esc_attr($theme->category->term_id)
        );
    }

    /**
     * Print title handlebars
     *
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return void
     */
    public static function showTitleHandlebars($config, $params)
    {
        $name = self::$themeName;
        if ($config['custom_icon']) {
            $html = '{{#if file_custom_icon}}<span class="icon-custom"><img src="{{file_custom_icon}}"></span>{{else}}<span class="fa fa-file-{{ext}}-o fa-2x mr-2"></span>{{/if}}';
        } else {
            $html = '<span class="fa fa-file-{{ext}}-o fa-2x mr-2"></span>';
        }
        /**
         * Filter to change icon html for handlebars template
         *
         * @param string Output html for handlebars template
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_icon_hanlebars
         *
         * @return string
         *
         * @ignore
         */
        $html = apply_filters('wpfd_' . $name . '_file_info_icon_hanlebars', $html, $config, $params);

        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1) {
            $selectFileInput = '<input class="cbox_file_download" type="checkbox" data-id="{{ID}}" />';
        }
        $template = array(
            'html' => $selectFileInput . '<a class="wpfd_downloadlink d-flex align-items-center" href="%link$s" title="%title$s">%icon$s<span>%croptitle$s</span></a>',
            'args' => array(
                'link'      => '{{linkdownload}}',
                'title'     => '{{post_title}}',
                'icon'      => $html,
                'croptitle' => '{{{crop_title}}}'
            )
        );
        /**
         * Filter to change html and arguments of title handlebars
         *
         * @param array Template array
         * @param array Main config
         * @param array Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_title_handlebars_args
         *
         * @return array
         *
         * @ignore
         */
        $args = apply_filters('wpfd_' . $name . '_file_info_title_handlebars_args', $template, $config, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render('<td data-title="Title" class="file_title col-sm-6 col-md-5">' . $args['html'] . '</td>', $args['args']);
    }

    /**
     * Print title
     *
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return void
     */
    public static function showTitle($file, $config, $params)
    {
		$name = self::$themeName;

        if ($config['custom_icon'] && isset($file->file_custom_icon) && $file->file_custom_icon !== '') {
            $args = array(
                'html' => '<span class="icon-custom">
                                <img src="%iconurl$s">
                                <span class="icon-custom-title">
                                    %croptitle$s
                                </span>
                            </span>',
                'args' => array(
                    'iconurl'   => esc_url($file->file_custom_icon),
                    'croptitle' => esc_html($file->crop_title)
                )
            );
        } else {
            $args = array(
                'html' => '<span class="fa fa-file-%class$s-o fa-2x mr-2"></span>
							%croptitle$s',
                'args' => array(
                    'class'     => esc_attr(strtolower($file->ext)),
                    'croptitle' => esc_html($file->crop_title)
                )
            );
        }
        /**
         * Filter to change icon html
         *
         * @param array  Template array
         * @param object Current file object
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_icon_html
         *
         * @return string
         *
         * @ignore
         */
        $args = apply_filters('wpfd_' . $name . '_file_info_icon_html', $args, $file, $config, $params);

        $icon     = self::render($args['html'], $args['args']);

        $selectFileInput = '';
        if ((int) $config['download_selected'] === 1) {
            $selectFileInput = '<input class="cbox_file_download" type="checkbox" data-id="' . $file->ID . '" />';
        }
        $template = array(
            'html' => $selectFileInput . '<a class="wpfd_downloadlink d-flex align-items-center" href="%link$s" title="%title$s">%icon$s</a>',
            'args' => array(
                'link'  => esc_url($file->linkdownload),
                'title' => esc_attr($file->post_title),
                'icon'  => $icon
            )
        );
        /**
         * Filter to change html and arguments of title
         *
         * @param array  Template array
         * @param object Current file object
         * @param array  Main config
         * @param array  Current category config
         *
         * @hookname wpfd_{$themeName}_file_info_title_args
         *
         * @return array
         *
         * @ignore
         */
        $args = apply_filters('wpfd_' . $name . '_file_info_title_args', $template, $file, $config, $params);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo self::render('<td data-title="Title" class="file_title col-sm-6 col-md-5">' . $args['html'] . '</td>', $args['args']);
    }

    /**
     * Callback for file description handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function descriptionHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Description" class="file_desc col">%value$s</td>',
            'args' => array(
                'value' => '{{{description}}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file description
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function description($args, $file, $config, $params)
    {
        $description = '';
        if (!empty($file->description)) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Used wpfd_esc_desc to remove <script>
            $description = wpfd_esc_desc($file->description);
        }
        $args = array(
            'html' => '<td data-title="Description" class="file_desc col">%value$s</td>',
            'args' => array(
                'value' => $description
            )
        );

        return $args;
    }

    /**
     * Callback for file version handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function versionHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Version" class="file_version col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => '{{versionNumber}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file version
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function version($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Version" class="file_version col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => esc_html(!empty($file->versionNumber) ? $file->versionNumber : '')
            )
        );

        return $args;
    }

    /**
     * Callback for file hits handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function hitsHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Hits" class="file_hits col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => '{{hits}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file hits
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function hits($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Hits" class="file_hits col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => esc_html($file->hits)
            )
        );

        return $args;
    }

    /**
     * Callback for file created handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function createdHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Date added" class="file_created col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => '{{created}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file created
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function created($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Date added" class="file_created col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => esc_html($file->created)
            )
        );

        return $args;
    }

    /**
     * Callback for file modified handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function modifiedHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Date modified" class="file_modified col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => '{{modified}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file modified
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function modified($args, $file, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Date modified" class="file_modified col-1 text-center">%value$s</td>',
            'args' => array(
                'value' => esc_html($file->modified)
            )
        );

        return $args;
	}

	    /**
     * Callback for file size handlebars
     *
     * @param array $args   Arguments
     * @param array $config Main config
     * @param array $params Current category config
     *
     * @return array
     */
    public static function sizeHandlebars($args, $config, $params)
    {
        $args = array(
            'html' => '<td data-title="Size" class="file_size col-1 text-right">%value$s</td>',
            'args' => array(
                'value' => '{{bytesToSize size}}'
            )
        );

        return $args;
    }

    /**
     * Callback for file size
     *
     * @param array  $args   Arguments
     * @param object $file   Current file object
     * @param array  $config Main config
     * @param array  $params Current category config
     *
     * @return array
     */
    public static function size($args, $file, $config, $params)
    {
        $fileSize = ($file->size === 'n/a') ? $file->size : WpfdHelperFile::bytesToSize($file->size);
        $args     = array(
            'html' => '<td data-title="Size" class="file_size col-1 text-right">%value$s</td>',
            'args' => array(
                'value' => esc_html($fileSize)
            )
        );

        return $args;
    }

    /**
     * Callback for print title column header
     *
     * @return void
     */
    public static function thTitle()
    {
        $name = self::$themeName;
        $html = '<th class="file_title col-sm-6 col-md-5">' . esc_html__('Title', 'wpfd') . '</th>';

        /**
         * Filter to change html header of title column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_title_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_title_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print description column header
     *
     * @return void
     */
    public static function thDesc()
    {
        $name = self::$themeName;
        $html = '<th class="file_desc col">' . esc_html__('Description', 'wpfd') . '</th>';

        /**
         * Filter to change html header of description column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_description_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_description_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print version column header
     *
     * @return void
     */
    public static function thVersion()
    {
        $name = self::$themeName;
        $html = '<th class="file_version col-1">' . esc_html__('Version', 'wpfd') . '</th>';

        /**
         * Filter to change html header of version column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_version_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_version_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print hits column header
     *
     * @return void
     */
    public static function thHits()
    {
        $name = self::$themeName;
        $html = '<th class="file_hits col-1">' . esc_html__('Hits', 'wpfd') . '</th>';

        /**
         * Filter to change html header of hits column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_hits_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_hits_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print created date column header
     *
     * @return void
     */
    public static function thCreated()
    {
        $name = self::$themeName;
        $html = '<th class="file_created col-1">' . esc_html__('Date added', 'wpfd') . '</th>';

        /**
         * Filter to change html header of created date column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_created_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_created_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print modified date column header
     *
     * @return void
     */
    public static function thModified()
    {
        $name = self::$themeName;
        $html = '<th class="file_modified col-1">' . esc_html__('Date modified', 'wpfd') . '</th>';

        /**
         * Filter to change html header of modified date column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_modified_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_modified_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

	/**
     * Callback for print size column header
     *
     * @return void
     */
    public static function thSize()
    {
        $name = self::$themeName;
        $html = '<th class="file_size col-1 text-right">' . esc_html__('Size', 'wpfd') . '</th>';

        /**
         * Filter to change html header of size column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_size_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_size_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
    }

    /**
     * Callback for print download column header
     *
     * @return void
     */
    public static function thDownload()
    {
        $name = self::$themeName;
        $html = '<th class="file_download col-auto">' . esc_html__('Download', 'wpfd') . '</th>';

        /**
         * Filter to change html header of download column
         *
         * @param string Header html
         *
         * @hookname wpfd_{$themeName}_column_download_header_html
         *
         * @return string
         */
        $output = apply_filters('wpfd_' . $name . '_column_download_header_html', $html);
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- this escaped
        echo $output;
	}

	public static function reorder_fileinfo() {

	}
}
