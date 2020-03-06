import $ from 'jquery';
window.jQuery = $;

export default class BlockCoreColumns {

  constructor() {

    this.initColumnCountCssClass();
  }

  initColumnCountCssClass = () => {

    const columnsBlocks = $('.wp-block-columns');

    if(columnsBlocks.length > 0) {
      columnsBlocks.each(function(index){

        const childCount = $(this).children().length;

        $(this).addClass(`has-${childCount}-columns`);

      });
    }

  };

}