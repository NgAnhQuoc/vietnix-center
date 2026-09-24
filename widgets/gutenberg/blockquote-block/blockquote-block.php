<?php
use VNXCenter\Gutenberge\Block\Blockquote;
if ( !defined( 'ABSPATH' ) )
    die( 'Direct access forbidden.' );
$blockquote = new Blockquote();
$blockquote->render();
?>