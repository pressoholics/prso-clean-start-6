import $ from 'jquery';
window.jQuery = $;

export default class PrsoLoadReactScripts {

  constructor() {
    this.initScriptLoader();
  }

  initScriptLoader = () => {

    if( !prsoThemeLocalVars.reactConfig ) {
      return;
    }

    const { reactScripts = [] } = prsoThemeLocalVars.reactConfig;

    if( reactScripts.length < 1 ) {
      return;
    }

    reactScripts.forEach(scriptURL => {

      $.getScript( scriptURL )
        .done(function (script, textStatus) {
        })
        .fail(function (jqxhr, settings, exception) {
          console.error( exception );
        });

    });

  };

}