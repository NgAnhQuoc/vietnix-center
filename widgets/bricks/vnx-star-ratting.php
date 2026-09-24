<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use HelperCenter\View;

class Star_rating_Center extends \Bricks\Element {
	public $category = 'vietnix';
	public $name     = 'star-rating';
	public $icon     = 'ti-star';

	public function get_label() {
		return esc_html__( 'VNX Star Rating', 'bricks' );
	}

	public function set_controls() {
        $this->controls['rating'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Rating', 'bricks' ),
            'type' => 'number',
            'spellcheck' => true, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => true,
            'default' => '4',
          ];
          $this->controls['star'] = [
            'tab' => 'content',
            'label' => esc_html__( 'Star Count', 'bricks' ),
            'type' => 'number',
            'spellcheck' => true, // Default: false
            // 'trigger' => 'enter', // Default: 'enter'
            'inlineEditing' => true,
            'default' => '5',
          ];
		$this->controls['icon'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Icon Full', 'bricks' ),
			'type'    => 'icon',
			'css'     => [
				[
					'selector' => '&.icon-svg', // NOTE: Undocumented: & = no space (add to element root)
				],
			],
			'default' => [
				'library' => 'themify',
				'icon'    => 'ti-star',
			],
		];
        $this->controls['iconhalf'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Icon Half', 'bricks' ),
			'type'    => 'icon',
			'css'     => [
				[
					'selector' => '&.icon-svg', // NOTE: Undocumented: & = no space (add to element root)
				],
			],
			'default' => [
				'library' => 'themify',
				'icon'    => 'ti-star',
			],
		];
        $this->controls['iconempty'] = [
			'tab'     => 'content',
			'label'   => esc_html__( 'Icon Empty', 'bricks' ),
			'type'    => 'icon',
			'css'     => [
				[
					'selector' => '&.icon-svg', // NOTE: Undocumented: & = no space (add to element root)
				],
			],
			'default' => [
				'library' => 'themify',
				'icon'    => 'ti-star',
			],
		];
		$this->controls['iconColor'] = [
			'tab'      => 'content',
			'label'    => esc_html__( 'Color', 'bricks' ),
			'type'     => 'color',
			'css'      => [
				[
					'property' => 'color',
				],
			],
			'required' => [ 'icon.icon', '!=', '' ],
		];

		$this->controls['iconSize'] = [
			'tab'         => 'content',
			'label'       => esc_html__( 'Size', 'bricks' ),
			'type'        => 'number',
			'units'       => true,
			'css'         => [
				[
					'property' => 'font-size',
				],
			],
			'placeholder' => '60px',
			'required'    => [ 'icon.icon', '!=', '' ],
		];

		$this->controls['_typography']['placeholder']['font-size']   = 60;
		$this->controls['_typography']['placeholder']['line-height'] = 1;
	}

	public function render() {
    View::render( "widgets/bricks/vnx-star-ratting", $this);  
	}
}