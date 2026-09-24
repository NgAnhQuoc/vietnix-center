const PolicyTemplate = {
  init() {
    jQuery(function(){
      var url = window.location.pathname;
      // create regexp to match current url
      var urlRegExp = new RegExp(url.replace(/\/$/,'') + "$");
      // now grab every link from the navigation
      jQuery('.page-policy .vnx-nav-left a').each(function(){
          // if not home page
          if(!urlRegExp.test('/$/')){
            // and test its normalized href against the url pathname regexp
            if(urlRegExp.test(this.href.replace(/\/$/,''))){
              jQuery(this).parent().addClass('active');
            }
          }
      });
    });
  },
};

export default PolicyTemplate;
