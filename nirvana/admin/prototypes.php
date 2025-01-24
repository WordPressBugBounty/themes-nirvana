<?php

// master function used for displaying font family / gfont / size selectors
function cryout_proto_font($fonts,$sizes,$size,$font,$gfont,$labelsize,$labelfont,$labelgfont,$general="",$custom=""){ ?>
	<?php if ($size>0): ?>
	<select id='<?php echo $labelsize; ?>' name='nirvana_settings[<?php echo $labelsize; ?>]' class='fontsizeselect'>
	<?php foreach($sizes as $item): ?>
		<option value='<?php echo $item; ?>' <?php selected($size,$item); ?>><?php echo $item; ?></option>
	<?php endforeach; ?>
	</select>
	<?php endif; ?>

	<select id='<?php echo $labelfont; ?>' class='admin-fonts fontnameselect' name='nirvana_settings[<?php echo $labelfont; ?>]'>
	<?php if ( !empty($general) || !empty($custom) ) { ?>
		<optgroup>
			<?php if (!empty($general)) { ?><option value="font-general" <?php selected($font,'font-general'); ?>><?php echo $general; ?></option><?php } ?>
			<?php if (!empty($custom)) { ?><option value="font-custom" <?php selected($font,'font-custom'); ?>><?php echo $custom; ?></option><?php } ?>
		</optgroup>
	<?php }
	foreach ($fonts as $fgroup => $fsubs): ?>
		<optgroup label='<?php echo $fgroup; ?>'>
		<?php foreach($fsubs as $item):
			$item_show = explode(',',$item); ?>
			<option style='font-family:<?php echo $item; ?>;' value='<?php echo $item; ?>' <?php selected($font,$item); ?>>
				<?php echo $item_show[0]; ?>
			</option>
		<?php endforeach; // fsubs ?>
		</optgroup>
	<?php endforeach; // fonts ?>
	</select>
	<input class="googlefonts" type="text" size="35" value="<?php echo esc_attr($gfont); ?>"  name="nirvana_settings[<?php echo $labelgfont; ?>]" id="<?php echo $labelgfont; ?>" placeholder = "<?php _e("or Google font","nirvana"); ?>"/>
<?php
} // cryout_proto_font()

function cryout_color_clean($color){
	if (strlen($color)>1): return "#".str_replace("#","",$color);
	else: return $color;
	endif;
} // cryout_color_clean()

function cryout_color_field($id,$title,$value,$hint=""){
	echo '<input type="text" id="'.$id.'" class="colorthingy" title="'.$title.'" name="nirvana_settings['.$id.']" value="'.esc_attr(cryout_color_clean($value)).'"  />';
    echo '<div id="'.$id.'2"></div>';
	if (strlen($hint)>0) echo "<div><small>".$hint."</small></div>";
} // cryout_color_field()


function cryout_proto_field($settings,$type,$name,$values,$labels='',$cls='',$echo=true){
	$data = ''; $len = 4; $san = 'str';
	if (preg_match("/input(\d{1,3})([a-z]{3})?/i",$type,$ms)):
		$type = "input";
		$len = $ms[1];
		if (isset($ms[2])): $san = $ms[2]; endif;
	endif;
	switch ($type):
		case "checkbox": 
			$data = "<input value='1' id='$name' name='{$settings['id']}[$name]' type='checkbox' ".checked($values,'1',0). " class='$cls'/> ".
			$data .= "<label for='$name' class='socialsdisplay'>";
			$data .= $labels." </label>\n";
		break; 
		case "select": 
			$data = "<select id='$name' name='{$settings['id']}[$name]' class='$cls'>";
			foreach($values as $id => $val):
				$data .= "<option value='$val'".selected($settings[$name],$val,false).">$labels[$id]</option>";
			endforeach;
			$data .= "</select>\n";	
		break;
		case "textarea": 
		
		break;	
		case "input":
		default:    
			$data = "<input id='$name' name='{$settings['id']}[$name]' size='$len' type='text' value='";
			switch ($san): 
				case "url": $data .= esc_url( $settings[$name] ); break; 
				case "int": $data .= intval(esc_attr( $settings[$name] )); break; 
				case "str": $data .= esc_attr( $settings[$name] ); break; 
			endswitch; 
			$data .=  "' class='$cls'/>$labels\n";
		break;
	endswitch;
	if ($echo): echo $data; else: return $data;  endif;
} //cryout_proto_field()

function cryout_color_sanitize( $color ) {
    if ( '' === $color ) return '';
	$color = trim(wp_kses_data($color));
 
    if ( preg_match( '/^#?([A-Fa-f0-9]{3}){1,2}$/', $color ) ) {
        return '#' . preg_replace( '/#/i', '', $color );
    }	
	return '';
} // cryout_color_sanitize()

function cryout_proto_arrsan($data){
	$filtered = array();
	foreach ($data as $key => $value):
		if (is_array($value)):
			$value = cryout_proto_arrsan($value);
		endif;
		if (is_numeric($value)): $filtered[esc_attr($key)] = esc_attr($value);
		else: $filtered[esc_attr($key)] = wp_kses_data($value);
		endif;
	endforeach;
	return $filtered;
} //cryout_proto_arrsan()

/**
 * Google font identifier cleanup
 */
function cryout_gfontclean( $gfont, $mode = 1 ) {
	switch ($mode) {
		case 2: // for custom styling
			return esc_attr(str_replace('+',' ',preg_replace('/[:&].*/','',$gfont)));
		break;
		case 1: // for font enqueuing
		default:
			return esc_attr(preg_replace( '/\s+/', '+',$gfont));
		break;
	} // switch
} // cryout_gfontcleanup()

////////// HELPER FUNCTIONS //////////

/**
 * Checks if a give variable is set to any of possible values
 */
function cryout_optset($var,$val1,$val2='',$val3='',$val4=''){
	$vals = array($val1,$val2,$val3,$val4);
	if (in_array($var,$vals)): return false; else: return true; endif;
} // cryout_optset()

/**
 * Font name cleanup for style output
 */
function cryout_fontname_cleanup( $fontid ) {
    // do not process non font ids
    if ( ( trim($fontid) == 'font-general') || (strtolower(trim($fontid)) == 'general font') ) return $fontid;
    $fontid = trim($fontid);
    $fonts = @explode(",", $fontid);
    // split multifont ids into fonts array
    if (is_array($fonts)){
        foreach ($fonts as &$font) {
            $font = trim($font);
            // if font has space in name, quote it
            if (strpos($font,' ')>-1) $font = '"' . $font . '"';
        };
        return implode(', ',$fonts);
    } elseif (strpos($fontid,' ')>-1) {
        // if font has space in name, quote it
        return '"' . $fontid . '"';
    } else return $fontid;
} // cryout_fontname_cleanup

function cryout_hex2rgb($hex) {
   $hex = str_replace("#", "", $hex);
   if (preg_match("/^([a-f0-9]{3}|[a-f0-9]{6})$/i",$hex)):
        if(strlen($hex) == 3) {
           $r = hexdec(substr($hex,0,1).substr($hex,0,1));
           $g = hexdec(substr($hex,1,1).substr($hex,1,1));
           $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
           $r = hexdec(substr($hex,0,2));
           $g = hexdec(substr($hex,2,2));
           $b = hexdec(substr($hex,4,2));
        }
        $rgb = array($r, $g, $b);
        return implode(",", $rgb); // returns the rgb values separated by commas
   else: return "";  // input string is not a valid hex color code
   endif;
} // cryout_hex2rgb()

function cryout_hexadder($hex,$inc) {
   $hex = str_replace("#", "", $hex);
   if (preg_match("/^([a-f0-9]{3}|[a-f0-9]{6})$/i",$hex)):
        if(strlen($hex) == 3) {
           $r = hexdec(substr($hex,0,1).substr($hex,0,1));
           $g = hexdec(substr($hex,1,1).substr($hex,1,1));
           $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
           $r = hexdec(substr($hex,0,2));
           $g = hexdec(substr($hex,2,2));
           $b = hexdec(substr($hex,4,2));
        }

		$rgb_array = array($r,$g,$b);
		$newhex="#";
		foreach ($rgb_array as $el) {
			$el+=$inc;
			if ($el<=0) { $el='00'; }
			elseif ($el>=255) {$el='ff';}
			else {$el=dechex($el);}
			if(strlen($el)==1)  {$el='0'.$el;}
			$newhex.=$el;
		}
		return $newhex;
   else: return "";  // input string is not a valid hex color code
   endif;
} // cryout_hexadder()

// FIN
