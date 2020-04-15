<?php

  /**
   * Allows searching for and displaying the statuses of a supplied list of orders
   *
   * @author Werner C. Bessinger
   */
  class SMOS_Order_List {
      /**
       * Init
       */
      public static function init() {
          self::search_input_page();
          wp_enqueue_style('smos_css', SMOS_URL . 'assets/search.css');
          add_action('wp_ajax_search_results', [__CLASS__, 'search_results']);
          add_action('wp_ajax_nopriv_search_results', [__CLASS__, 'search_results']);
      }

      /**
       * Search input page
       */
      public static function search_input_page() {

          /**
           * Adds a submenu page under a custom post type parent.
           */
          function smos_register_settings_page() {
              add_submenu_page(
              'woocommerce',
              __('Bulk Order Status Search', 'woocommerce'),
                 __('Order Status Search', 'woocommerce'),
                    'manage_options',
                    'smos-order-status-search',
                    'smos_render_order_status_search_input'
              );
          }

          /**
           * Display callback for the submenu page.
           */
          function smos_render_order_status_search_input() {
              ?>
              <div class="wrap">

                <!-- smos input cont -->
                <div id="smos_input_cont">
                  <h1><?php _e('BULK ORDER STATUS SEARCH', 'woocommerce'); ?></h1>
                  <label for="smos_order_numbers_input"><?php echo __('Enter order numbers below. Each order number should be on a new line.'); ?></label>
                  <textarea id="smos_order_numbers_input" rows="8" name="smos_order_numbers_input"></textarea>
                  <br>
                  <!-- smos submit -->
                  <button id="smos_submit" class="button button-primary"><?php echo __('SEARCH', 'woocommerce'); ?></button>
                </div>

                <!-- smos result cont -->
                <div id="smos_result_cont">
                  <h1><?php _e('SEARCH RESULTS', 'woocommerce'); ?></h1>

                  <!-- smos results actual -->
                  <div id="smos_results_actual">
                    <table id="sboma_search_results_table" class="wp-list-table widefat fixed striped posts">
                      <thead>
                        <tr>
                          <th scope="col" id="order_number"><b>Order</b></th>
                          <th scope="col" id="order_date"><b>Date</b></th>
                          <th scope="col" id="order_status"><b>Status</b></th>
                          <th scope="col" id="order_total"><b>Total</b></th>
                          <th scope="col" id="order_payment"><b>Payment Method</b></th>
                        </tr>
                      </thead>
                      <tbody>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <?php
              /* enqueue js and css */
              wp_enqueue_script('smos_js', SMOS_URL . 'assets/search.js', ['jquery'], '1.0.0', true);
          }

          add_action('admin_menu', 'smos_register_settings_page', 99);
      }

      /**
       * Render search results (generated via AJAX)
       */
      public static function search_results() {

          if (!empty($_POST)):

              /* get submitted order numbers */
              $order_numbers = $_POST['order_nos'];

              /* preg split into usable array */
              $order_nos_arr = preg_split("/\r\n|\n|\r/", $order_numbers);

              /* empty order id array */
              $order_id_arr = [];

              /* query orders using submitted order numbers as meta query */
              $orderq = new WP_Query([
                  'post_type'      => 'shop_order',
                  'posts_per_page' => 50,
                  'post_status'    => 'any',
                  'meta_query'     => [
                      [
                          'key'     => '_order_number_formatted',
                          'value'   => $order_nos_arr,
                          'compare' => 'IN'
                      ]
                  ]
              ]);

              /* push order ids to order id array if orders are found */
              if ($orderq->have_posts()):
                  while ($orderq->have_posts()):
                      $orderq->the_post();

                      $order_id_arr[] = get_the_ID();

                  endwhile;
                  wp_reset_postdata();

              /* display error if no orders found */
              else:
                  $order_id_arr = [];
                  echo __('No orders found matching your search criteria. Please try again.', 'woocommerce');
              endif;

              /* loop through order ID arr if order ids present */
              if (!empty($order_id_arr) && is_array($order_id_arr) || is_object($order_id_arr)):
                  ?>

                  <!-- sboma search results table -->
                  <table id="sboma_search_results_table" class="wp-list-table widefat fixed striped posts">
                    <thead>
                      <tr>
                        <th scope="col" id="order_number"><b>Order</b></th>
                        <th scope="col" id="order_date"><b>Date</b></th>
                        <th scope="col" id="order_status"><b>Status</b></th>
                        <th scope="col" id="order_total"><b>Total</b></th>
                        <th scope="col" id="order_payment"><b>Payment Method</b></th>
                      </tr>
                    </thead>
                    <tbody>
                        <?php
                        foreach ($order_id_arr as $order_id) :
                            /* get order data */
                            $order_data = wc_get_order($order_id);
                            ?>
                          <tr>
                            <!-- order number, name and edit link -->
                            <td>
                              <a class="order-view" href="<?php echo get_edit_post_link($order_id); ?>" target="_blank"><?php echo $order_data->get_order_number() . ' ' . $order_data->get_billing_first_name() . ' ' . $order_data->get_billing_last_name(); ?></a>
                            </td>
                            <!-- order date -->
                            <td><?php echo get_the_date('', $order_id); ?></td>

                            <!-- order status -->
                            <td>
                              <span class="order-status status-<?php echo $order_data->get_status(); ?>"><?php echo wc_get_order_status_name($order_data->get_status()); ?></span>
                            </td>

                            <!-- order total -->
                            <td><?php echo $order_data->get_currency() . ' ' . $order_data->get_total(); ?></td>

                            <!-- payment method -->
                            <td><?php echo $order_data->get_payment_method_title(); ?></td>
                          </tr>

                      <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php
              endif;
              wp_die();
          endif;
      }

  }

  SMOS_Order_List::init();
  