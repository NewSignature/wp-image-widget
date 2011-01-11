# WP Image Widget

This wraps in the functionality to use images within your image by using the WordPress media
library functionality. 

The credit for most of the logic for this class goes to [Shane and Peter, Inc.](http://www.shaneandpeter.com)
in their [Image Widget plugin](http://wordpress.org/extend/plugins/image-widget/).

## How to Use 

Include this file into your widget plugin and then have your widget class extend WP_Image_Widget
instead of WP_Widget.

    class MyWidget extends WP_Image_Widget { ... }

Use the class as you would for any other widget with the exception of two addition methods
for your use.

### wpiw_get_image_field( name, instance)

Use this when creating the widget form (in the from() method). Just pass in the name of the field
and the instance array that was passed into the form() method. It will return the HTML to add to
your form.

    echo $this->wpiw_get_image_field( 'my_image', $instance );

### wpiw_get_image_output( name, instance [,options]
Use this when output the widget (in the widget() method). Just pass in the name of the field,
the instance array that was passed into the widget() method, and the optional options array. 
You can change the size of the output image with the size option just as you would for a post
thumbnail. 

    echo $this->wpiw_get_image_output( 'my_image', $instance,  array( 'size' => 'my_custom_defined_size' ) );

## License

Copyright New Signature 2010 - 2011

This program is free software: you can redistribute it and/or modify it under the terms of the 
GNU General Public License as published by the Free Software Foundation, either version 3 of the 
License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; 
without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program.  
If not, see <http://www.gnu.org/licenses/>.

You can contact New Signature by electronic mail at labs@newsignature.com 
or- by U.S. Postal Service at 1100 H St. NW, Suite 940, Washington, DC 20005.