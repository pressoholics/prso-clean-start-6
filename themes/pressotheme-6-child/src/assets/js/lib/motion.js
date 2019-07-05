import $ from 'jquery';
window.jQuery = $;

export default class PrsoMotion {

  /**
   * smoothScrollTo
   *
   * helper to smooth scroll to element on page
   *
   * @access public
   * @author Ben Moody
   */
   smoothScrollTo( scrollToElement ) {

    if( scrollToElement.length < 1 ) {
      return;
    }

    const new_position = scrollToElement.offset();

    $('html, body').stop().animate({ scrollTop: new_position.top }, 500);

  }

}