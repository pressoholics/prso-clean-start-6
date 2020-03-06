import $ from 'jquery';
window.jQuery = $;

export default class PrsoAccessability {

  constructor() {

    this.initFontToggle();

    //Detect accessability input
    window.addEventListener('keydown', this.handleFirstTab);

  }

  /**
   * initFontToggle
   *
   * init font toggle actions
   *
   * @access public
   * @author Ben Moody
   */
  initFontToggle = () => {

    const fontSizeToggle = $('.toggle-fontsize');

    if( fontSizeToggle.length < 1 ) {
      return;
    }

    fontSizeToggle.on('click', function(){

      //Recalc sticky main nav container size
      setTimeout(function(){ $('#header-container').foundation('_calc', true); }, 200);

    });

  };

  handleFirstTab = (e) => {

    if (e.keyCode === 9) { // the "I am a keyboard user" key
      document.body.classList.add('user-is-tabbing');
      window.removeEventListener('keydown', this.handleFirstTab);
    }

  };

}