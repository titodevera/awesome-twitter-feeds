<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>
<p><input class="checkbox" type="checkbox"<?php checked($show_title); ?> id="<?php echo $this->get_field_id('show_title'); ?>" name="<?php echo $this->get_field_name('show_title'); ?>" /><label for="<?php echo $this->get_field_id('show_title'); ?>"><?php _e('Show title'); ?></label></p>
<p>
    <label for="<?php echo $this->get_field_id('widget'); ?>"><?php _e('Widget:'); ?></label>
    <select id="<?php echo $this->get_field_id('widget'); ?>" name="<?php echo $this->get_field_name('widget'); ?>">
        <?php
        foreach($available_widgets as $widget_array){
            $is_selected = selected($widget,$widget_array['post_id'],false);
            echo '<option value="'.$widget_array['post_id'].'" '.$is_selected.'>';
            echo $widget_array['post_title'];
            echo '</option>';
        }
        ?>
    </select>
</p>