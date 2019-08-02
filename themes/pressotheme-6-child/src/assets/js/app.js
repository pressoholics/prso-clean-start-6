import $ from 'jquery';
import 'what-input';

import PrsoLoadReactScripts from './lib/load-react-scripts';

//Import motion
//import MotionHelper from './lib/motion';

// Foundation JS relies on a global varaible. In ES6, all imports are hoisted
// to the top of the file so if we used`import` to import Foundation,
// it would execute earlier than we have assigned the global variable.
// This is why we have to use CommonJS require() here since it doesn't
// have the hoisting behavior.
window.jQuery = $;
require('foundation-sites');

// If you want to pick and choose which modules to include, comment out the above and uncomment
// the line below
//import {Tabs,Accordion} from './lib/foundation-explicit-pieces';

$(document).foundation();

$(document).ready(function() {

  //Maybe load react scripts
  new PrsoLoadReactScripts();

});