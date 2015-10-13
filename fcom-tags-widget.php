<?
class fcom_tags_widget extends WP_Widget {

	// constructor
	function fcom_tags_widget() {
		parent::__construct(false, $name = __('Fcom - Mapa de entradas', 'fcom_tags_widget') );
	}

	// widget form creation
	function form($instance) {	
        // Check values
        if( $instance) {
             //$title = esc_attr($instance['title']);
        } else {
             //$title = '';
        }
        ?>

        <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Widget Title', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>

        <p>
        <label for="<?php echo $this->get_field_id('text'); ?>"><?php _e('Text:', 'wp_widget_plugin'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('text'); ?>" name="<?php echo $this->get_field_name('text'); ?>" type="text" value="<?php echo $text; ?>" />
        </p>

        <p>
        <label for="<?php echo $this->get_field_id('textarea'); ?>"><?php _e('Textarea:', 'wp_widget_plugin'); ?></label>
        <textarea class="widefat" id="<?php echo $this->get_field_id('textarea'); ?>" name="<?php echo $this->get_field_name('textarea'); ?>"><?php echo $textarea; ?></textarea>
        </p>
        <?php
        }

	// widget update
    function update($new_instance, $old_instance) {
          $instance = $old_instance;
          // Fields
          /*
          $instance['title'] = strip_tags($new_instance['title']);
          */
         return $instance;
    }

	// widget display
    function widget($args, $instance) {
       echo $before_widget;
       ?>
       <div class="leaflet-control-container">
       <div class="leaflet-top leaflet-left has-leaflet-pan-control">
            <div class="pan-controls leaflet-control-pan leaflet-control">
                <div class="pan-control pan-control-up leaflet-control-pan-up-wrap">
                    <button id="panUp" type="button" class="btn btn-default btn-pan btn-xs">
                        <span class="glyphicon glyphicon-arrow-up leaflet-control-pan-up" aria-hidden="true" title="Arriba"></span>
                    </button>
                </div>
                <div class="pan-control pan-control-left leaflet-control-pan-left-wrap">
                    <button id="panLeft" type="button" class="btn btn-default btn-pan btn-xs">
                        <span class="glyphicon glyphicon-arrow-left leaflet-control-pan-left" aria-hidden="true" title="Izquierda"></span>
                    </button>
                </div>
                <div class="pan-control pan-control-right leaflet-control-pan-right-wrap">
                    <button id="panRight" type="button" class="btn btn-default btn-pan btn-xs">
                        <span class="glyphicon glyphicon-arrow-right leaflet-control-pan-right" aria-hidden="true" title="Derecha"></span>
                    </button>
                </div>
                <div class="pan-control pan-control-down leaflet-control-pan-down-wrap">
                    <button id="panDown" type="button" class="btn btn-default btn-pan btn-xs">
                        <span class="glyphicon glyphicon-arrow-down leaflet-control-pan-down" aria-hidden="true" title="Abajo"></span>
                    </button>
                </div>
            </div>
            <div class="btn-group-vertical zoom-controls leaflet-control-zoom leaflet-bar leaflet-control" role="group">
                <button id="zoomIn" type="button" class="btn btn-default btn-pan btn-sm">
                    <span class="glyphicon glyphicon-zoom-in" aria-hidden="true" title="Zoom in"></span>
                </button>
                <button id="zoomOut" type="button" class="btn btn-default btn-pan btn-sm">
                    <span class="glyphicon glyphicon-zoom-out" aria-hidden="true" title="Zoom out"></span>
                </button>
            </div>
        </div>
        </div>
        <div id="fcom-mapa" style="width:930px;height:500px;"></div>
        <?
        echo $after_widget;
        wp_enqueue_script('fcom-tags-mapa-js', plugins_url('/js/fcom_mapa.ribbon.js', __FILE__));
       
    }
}

// register widget
add_action('widgets_init', create_function('', 'return register_widget("fcom_tags_widget");'));
