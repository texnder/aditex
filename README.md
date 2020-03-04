# aditex
automatic dependency injector(PHP)

## autowiring
aditex is a PHP dependency injector which uses autowiring method for inversion control and to inject required dependencies in methods

## uses core PHP reflaction class

aditex uses core PHP reflaction class for creating object instances

PHP reflaction classes are standard inbuilt classes when talking about security. popular php framework like laravel uses reflaction class for dependency injection..

## typehinting 
aditex uses autowiring method, so it quite required to typehint your dependencies in class methods to inject automatically..

## recursive 
aditex checks method dependencies recursively. that's why there is no limit of levels for dependency injection.

## pass arguments manually or uses defaults

developer can pass arguments manually or method will use default values. there is no restriction for constructor arguments. developer can pass other arguments values manually and aditex will take care of any required dependencies.

there is no need to create dependency instance manually and passed them to method arguments. aditex Container will take care of all required dependencies. 

**note** developer needs to push every manual data for method or dependencies constructor while calling aditex Container for getting class instance or executing any class method.

## any queries
for any further queries, please email us on: (texnder.components@gmail.com)

## License

The aditex is open-sourced php library licensed under the [MIT license](http://opensource.org/licenses/MIT).
