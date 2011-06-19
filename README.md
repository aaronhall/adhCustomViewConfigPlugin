This symfony plugin allows you to create custom configuration variables in `view.yml` at the app-, module- and action-level. Inheritance works as expected. Custom keys should be added under the `.custom` key in `view.yml`. Custom config data is appended to the default `view.yml` cache file.

This plugin overrides the default symfony view config handler (`sfViewConfigHandler`). If you're implementing a custom view config handler, your class should extend `adhViewConfigHandler` for this to work in tandem with yours (though, this hasn't been tested).

Examples
--------

In `apps/myApp/config/view.yml`:

    default:
      ## existing configs ##
      
      .custom:
        foo: bar

This variable is now available from sfConfig. Keys are always prefixed by `view_custom_`:

    sfConfig::get('view_custom_foo');  // returns 'bar'

Config parameters can be set at the module and action level as well. In `apps/myApp/modules/foo/config/view.yml`:

    # module level
    all:
      .custom:
        my_config: foo
    
    # action template level
    indexSuccess:
      .custom:
        my_config: bar

From `apps/myApp/modules/foo/actions/actions.class.php`:
    
    class fooActions extends sfActions {
    
      public function executeIndex(sfWebRequest $request) {
        sfConfig::get('view_custom_my_config'); // returns 'bar', overridden by 'indexSuccess' setting
      }
      
      public function executeAnotherAction(sfWebRequest $request) {
        sfConfig::get('view_custom_my_config'); // returns 'foo' from 'all' setting
      }
      
    }