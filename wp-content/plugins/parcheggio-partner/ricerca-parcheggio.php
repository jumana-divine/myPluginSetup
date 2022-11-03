<?php

class Ricerca_Ricerca_Widget extends WP_Widget {


  // Set up the widget name and description.
  public function __construct() {
    $widget_options = array( 'classname' => 'parcheggio_widget', 'description' => 'Ricerca Parcheggio' );
    parent::__construct( 'parcheggio_widget', 'Ricerca Parcheggio', $widget_options );
  }


  // Create the widget output.
  public function widget( $args, $instance ) {
    $title = apply_filters( 'widget_title', $instance[ 'title' ] );

    echo $args['before_widget'] . $args['before_title'] . $title . $args['after_title']; ?>

    <?php

    echo do_shortcode('[ricerca css="partner-sidebar" column="6" columndate="6" offset="0" button="12" top="20"]');

    echo $args['after_widget'];
  }


  // Create the admin area widget settings form.
  public function form( $instance ) {
    $title = ! empty( $instance['title'] ) ? $instance['title'] : ''; ?>
    <p>
      <label for="<?php echo $this->get_field_id( 'title' ); ?>">Title:</label>
      <input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $title ); ?>" />
    </p><?php
  }


  // Apply settings to the widget instance.
  public function update( $new_instance, $old_instance ) {
    $instance = $old_instance;
    $instance[ 'title' ] = strip_tags( $new_instance[ 'title' ] );
    return $instance;
  }

}

// Register the widget.
function Ricerca_register_ricerca_widget() {
  register_widget( 'Ricerca_Ricerca_Widget' );
}
add_action( 'widgets_init', 'Ricerca_register_ricerca_widget' );




?>