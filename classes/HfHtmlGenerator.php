<?php
if ( ! defined( 'ABSPATH' ) ) exit;
class HfHtmlGenerator implements Hf_iMarkupGenerator {
    private $cms;

    function __construct(
        Hf_iCms $contentManagementSystem,
        Hf_iAssetLocator $assetLocator
    ) {
        $this->cms = $contentManagementSystem;
        $this->assetLocator = $assetLocator;
    }

    public function tabs( $contents, $defaultTabNumber ) {
        $html = '[su_tabs active="' . $defaultTabNumber . '"]';

        foreach ( $contents as $title => $content ) {
            $html .= '[su_tab title="' . $title . '"]' . $content . '[/su_tab]';
        }

        return $this->cms->expandShortcodes( $html . '[/su_tabs]' );
    }

    public function element($tag, $content, $properties = array()) {
        $propertyString = $this->parseProperties($properties);
        return ($content !== null) ? "<$tag$propertyString>$content</$tag>" : "<$tag$propertyString />";
    }

    public function span($content, $properties) {
        return $this->element('span', $content, $properties);
    }

    public function paragraph( $content, $classes = NULL ) {
        $properties = ($classes) ? array('class'=>$classes) : array();
        return $this->element('p',$content,$properties);
    }

    public function div( $content, $classes = NULL ) {
        $properties = ($classes) ? array('class'=>$classes) : array();
        return $this->element('div',$content,$properties);
    }

    public function linkMarkup( $target, $content ) {
        $properties = array('href'=>$target);
        return $this->element('a',$content,$properties);
    }

    public function listMarkup( $items ) {
        $html = '';
        foreach ( $items as $item ) {
            $html .= $this->element('li',$item);
        }

        return $this->element('ul',$html);
    }

    public function errorMessage( $content ) {
        return $this->paragraph($content, 'error');
    }

    public function successMessage( $content ) {
        return $this->paragraph($content, 'success');
    }

    public function quotation( $quotation ) {
        $content = '"' . $quotation->post_content . '" — ' . $quotation->post_title;
        return $this->paragraph($content,'quote');
    }

    public function infoMessage( $content ) {
        return $this->paragraph($content, 'info');
    }

    public function form( $url, $content, $name ) {
        $properties = array('action'=>$url,'method'=>'post','name'=>$name);
        return $this->element('form',$content,$properties);
    }

    public function input($properties) {
        return $this->element('input',null,$properties);
    }

    public function buttonInput( $name, $label, $onclick ) {
        $properties = array('type'=>'button','name'=>$name, 'value'=>$label, 'onclick'=>$onclick);
        return $this->input($properties);
    }

    public function hiddenField( $name ) {
        $properties = array('type'=>'hidden','name'=>$name);
        return $this->input($properties);
    }

    public function redirectScript($url) {
        return $this->element('script',"window.location.replace('$url');");
    }

    public function refreshScript() {
        $url = $this->assetLocator->getCurrentPageUrl();
        return $this->redirectScript($url);
    }

    public function head($content, $level) {
        return $this->element("h$level",$content);
    }

    private function parseProperties($properties)
    {
        $propertyString = '';
        foreach ($properties as $property => $value) {
            $propertyString .= " $property='$value'";
        }
        return $propertyString;
    }

    public function label($content, $properties) {
        return $this->element('label', $content, $properties);
    }
}