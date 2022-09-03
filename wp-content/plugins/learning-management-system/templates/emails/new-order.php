<?php
/**
 * Admin new order email
 *
 * This template can be overridden by copying it to yourtheme/masteriyo/emails/new-order.php.
 *
 * HOWEVER, on occasion masteriyo will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @package masteriyo\Templates\Emails\HTML
 */

defined( 'ABSPATH' ) || exit;

/**
 * Fires before rendering email header.
 *
 * @since 1.0.0
 *
 * @param string $email_heading Email header.
 * @param \Masteriyo\Emails\Email $email Email object.
 */
do_action( 'masteriyo_email_header', $email_heading, $email ); ?>

<p>
	<?php
		printf(
			/* translators: %s: Customer billing full name */
			esc_html__( 'You’ve received the following order from %s:', 'masteriyo' ),
			esc_html( $order->get_formatted_billing_full_name() )
		);
		?>
</p>

<?php
/**
 * Action hook for rendering order details in new order email.
 *
 * @since 1.0.0
 *
 * @param \Masteriyo\Models\Order\Order $order Order object.
 * @param \Masteriyo\Emails\Email $email Email object.
 */
do_action( 'masteriyo_email_order_details', $order, $email );

/**
 * Action hook for rendering order meta in new order email.
 *
 * @since 1.0.0
 *
 * @param \Masteriyo\Models\Order\Order $order Order object.
 * @param \Masteriyo\Emails\Email $email Email object.
 */
do_action( 'masteriyo_email_order_meta', $order, $email );

/**
 * Action hook for rendering customer details in new order email.
 *
 * @since 1.0.0
 *
 * @param \Masteriyo\Models\Order\Order $order Order object.
 * @param \Masteriyo\Emails\Email $email Email object.
 */
do_action( 'masteriyo_email_customer_details', $order, $email );

/**
 * Action hook fired in email's footer section.
 *
 * @since 1.0.0
 *
 * @param \Masteriyo\Emails\Email $email Email object.
 */
do_action( 'masteriyo_email_footer', $email );
