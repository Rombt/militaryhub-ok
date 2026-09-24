<?php
/*
 * Author: Andrii K (andrey.kovt@gmail.com)
 * Date: 12.07.2021
 * Time: 15:34:59
 */

chdir( __DIR__ );
require_once( 'api/Okay.php' );
$okay = new Okay();

$lang_id = $okay->languages->lang_id();
$language = $okay->languages->get_language( $lang_id );
$lang_link = '';
if ( ! empty( $language ) ) {
	$lang_link = $okay->languages->get_lang_link();
}
$main_url = $okay->config->root_url . '/' . $lang_link;

$currencies = $okay->money->get_currencies( array( 'enabled' => 1 ) );
$currency = reset( $currencies );

$px = ( $lang_id ? 'lp' : 'c' );
$cx = ( $lang_id ? 'lc' : 'c' );
$bx = ( $lang_id ? 'lb' : 'b' );
$vx = ( $lang_id ? 'lv' : 'v' );

$okay->db->query( "SELECT
    v.id as variant_id,
    MAX($vx.name) as variant_name,
    v.currency_id,
    IFNULL(v.stock, ?) as stock, 
    (v.stock IS NULL) as infinity, 
    v.compare_price,
    v.sku,
    v.price,
    v.color,
    v.size,
    v.position as variant_position,
    v.product_id as product_id,
    MAX($px.name) as product_name,
    MAX($px.annotation) as annotation,
    MAX($px.description) as description,
    p.url,
    pc.category_id,
    i.filename as image,
    MAX($bx.name) as vendor,
    $cx.name as category_name,
    c.google_name as google_category_name,
    c.facebook_name as facebook_category_name,    /* //!! */
	 p.created as product_created		/* //!! */
FROM __variants v
LEFT JOIN __lang_variants lv ON lv.variant_id = v.id AND lv.lang_id = ?
INNER JOIN __products p ON v.product_id = p.id
LEFT JOIN __lang_products lp ON lp.product_id = v.product_id AND lp.lang_id = ?
INNER JOIN __brands b ON p.brand_id = b.id AND b.visible
LEFT JOIN __lang_brands lb ON lb.brand_id = b.id AND lb.lang_id = ?
INNER JOIN __images i ON v.product_id = i.product_id
INNER JOIN __products_categories pc ON (v.product_id = pc.product_id AND pc.position = (SELECT MIN(position) FROM __products_categories WHERE product_id = v.product_id LIMIT 1))
INNER JOIN __categories c ON c.id = pc.category_id
LEFT JOIN __lang_categories lc ON lc.category_id = c.id AND lc.lang_id = ?
WHERE 1
    AND p.visible
    AND p.url <> ''
    AND (v.stock > 0 OR v.stock is NULL)
    AND v.feed_rss
GROUP BY v.id
ORDER BY p.id DESC", $okay->settings->max_order_amount, intval( $lang_id ), intval( $lang_id ), intval( $lang_id ), intval( $lang_id ) );
$products = $okay->db->results();


$p_ids = array();
$p_colors = array();
foreach ( $products as $p ) {
	if ( ! in_array( $p->product_id, $p_ids ) ) {
		$p_ids[] = $p->product_id;
	}
	if ( ! in_array( $p->color, $p_colors ) ) {
		$p_colors[] = $p->color;
	}
}

$p_images = array();
foreach ( $okay->products->get_images( array( 'product_id' => $p_ids ) ) as $image ) {
	$p_images[ $image->product_id ][] = $image->filename;
}

$p_v_images = array();
if ( $variants_images = $okay->variants->get_images( array( 'product_id' => $p_ids, 'color' => $p_colors, 'group_by' => 'variant' ) ) ) {
	foreach ( $variants_images as $image ) {
		if ( ! isset( $p_v_images[ $image->product_id ] ) ) {
			$p_v_images[ $image->product_id ] = array();
		}
		if ( ! isset( $p_v_images[ $image->product_id ][ $image->color ] ) ) {
			$p_v_images[ $image->product_id ][ $image->color ] = array();
		}
		$p_v_images[ $image->product_id ][ $image->color ][] = $image->filename;
	}
}

header( "Content-type: text/xml; charset=UTF-8" );
print ( pack( 'CCC', 0xef, 0xbb, 0xbf ) );
print "<?xml version=\"1.0\" ?>\n";
print "<rss version=\"2.0\" xmlns:g=\"http://base.google.com/ns/1.0\">\n";
print "\t<channel>\n";
print "\t\t<title>" . $okay->settings->site_name . "</title>\n";
print "\t\t
      <link>" . mb_substr( $main_url, 0, mb_strlen( $main_url ) - 1 ) . "</link>\n";
print "\t\t<description>" . $okay->settings->company_name . "</description>\n";

$now = strtotime( date( "Y-m-d H:i:s" ) );
foreach ( $products as $product ) {
	$variant_url = '';
	if ( $prev_product_id === $product->product_id ) {
		continue;
		$variant_url = '?variant=' . $product->variant_id;
	}

	print "\t\t<item>\n";
	print "\t\t\t<g:id>" . $product->variant_id . "</g:id>\n";
	print "\t\t\t<g:title>" . trim( htmlspecialchars( $product->product_name ) ) . "</g:title>\n";
	print "\t\t\t<g:description>
            <![CDATA[" . htmlspecialchars( strip_tags( $product->description ) ) . "]]>
         </g:description>\n";
	print "\t\t\t<g:link>" . $main_url . "products/" . $product->url . $variant_url . "</g:link>\n";

	if ( $prev_product_id !== $product->product_id ) {
		$images = array();
	}
	if ( isset( $p_v_images[ $product->product_id ] ) && isset( $p_v_images[ $product->product_id ][ 
		$product->color ] ) ) {
		$images[ $product->color ] = [ 
			'urls' => $p_v_images[ $product->product_id ][ $product->color ],
			'dir' => $okay->config->resized_variants_dir,
		];
	} elseif ( isset( $p_v_images[ $product->product_id ] ) ) {
		$images[ $product->color ] = [ 
			'urls' => $p_images[ $product->product_id ],
			'dir' => $okay->config->resized_images_dir,
		];
	} elseif ( $prev_product_id !== $product->product_id && isset( $p_images[ $product->product_id ] ) ) {
		$images[ $product->color ] = [ 
			'urls' => $p_images[ $product->product_id ],
			'dir' => $okay->config->resized_images_dir,
		];
	}

	if ( isset( $images[ $product->color ] ) ) {
		$dir = $images[ $product->color ]['dir'];


		//!!
		print "\t\t\t<g:image_link>" . $okay->design->resize_modifier( $images[ $product->color ]['urls'][0], 800, 800,
			false, $dir ) . "
         </g:image_link>\n";
		print "\t\t\t<g:additional_image_link>";


		if ( is_array( $images[ $product->color ]['urls'] ) ) {
			foreach ( $images[ $product->color ]['urls'] as $i => $image ) {
				if ( $i == 0 ) {
					continue;
				}
				print $okay->design->resize_modifier( $image, 800, 800, false, $dir ) . ',';
				if ( $i > 20 ) {
					break;
				}
			}
		}


		print "</g:additional_image_link>\n";

	} else {
		print "\t\t\t<g:image_link>" . $okay->design->resize_modifier( $product->image, 800, 800 ) . "</g:image_link>
         \n";
	}

	$prev_product_id = $product->product_id;

	if ( $product->stock > 0 ) {
		$availability = "in stock";
	} else {
		$availability = "out of stock";
	}
	print "\t\t\t<g:availability>" . $availability . "</g:availability>\n";
	if ( isset( $currencies[ $product->currency_id ] ) ) {
		$curr = $currencies[ $product->currency_id ];
	} else {
		$curr = $currency;
	}
	$price = round( $okay->money->convert( $product->price, $curr->id, false ), 2 );
	$compare_price = round( $okay->money->convert( $product->compare_price, $curr->id, false ), 2 );
	$compare_price = ( $compare_price > $price ) ? $compare_price : $price;
	print "\t\t\t<g:price>" . number_format( $compare_price, 2, '.', '' ) . ' ' . $curr->code . "</g:price>\n";
	print "\t\t\t<g:sale_price>" . number_format( $price, 2, '.', '' ) . ' ' . $curr->code . "</g:sale_price>\n";
	if ( ! empty( $product->google_category_name ) ) {
		$product->google_category_name = explode( '-', $product->google_category_name );
	}
	print "\t\t\t<g:google_product_category>" . trim( htmlspecialchars( $product->google_category_name[1] ) ) . "
         </g:google_product_category>\n";

	//!!
	print "\t\t\t<g:fb_product_category>" . trim( htmlspecialchars( $product->facebook_category_name ) )
		. "</g:fb_product_category>\n";

	print "\t\t\t<g:brand>" . trim( htmlspecialchars( $product->vendor ) ) . "</g:brand>\n";
	print "\t\t\t<g:mpn>" . trim( htmlspecialchars( $product->sku ) ) . "</g:mpn>\n";
	print "\t\t\t<g:color>" . trim( htmlspecialchars( $product->color ) ) . "</g:color>\n";
	print "\t\t\t<g:size>" . trim( htmlspecialchars( $product->size ) ) . "</g:size>\n";
	print "\t\t\t<g:item_group_id>" . $product->product_id . "</g:item_group_id>\n";
	print "\t\t\t<g:condition>new</g:condition>\n";
	print "\t\t\t<g:product_type>" . trim( htmlspecialchars( $product->category_name ) ) . "</g:product_type>\n";

	//!!
	$sku = strstr( $product->sku, "*", false );
	$sku = ltrim( strstr( $sku, "*" ), "*" );
	if ( $sku ) {
		print "\t\t\t<g:custom_label_0>" . trim( htmlspecialchars( $sku ) ) . "</g:custom_label_0>\n";
	}
	//!!
	if ( $product->product_created < '2021-12-31' ) {
		print "\t\t\t<g:custom_label_1>sale_70</g:custom_label_1>\n"
		;
	}
	print "\t\t</item>\n";
}
print "\t
   </channel>\n";
print "</rss>\n";
exit();