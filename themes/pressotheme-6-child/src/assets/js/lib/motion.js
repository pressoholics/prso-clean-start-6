
export default class PrsoMotion {

  /**
   * smooth_scroll_to
   *
   * helper to smooth scroll to element on page
   *
   * @access public
   * @author Ben Moody
   */
   smooth_scroll_to( scrollToElement ) {

    if( scrollToElement.length < 1 ) {
      return;
    }

    const new_position = scrollToElement.offset();

    $('html, body').stop().animate({ scrollTop: new_position.top }, 500);

  }

}