###Flyingpiranhas Common library

####Introduction
The FlyingPiranhas common library is part of the [FlyingPiranhas](http://www.flyingpiranhas.net) wireframework. It helps [me](http://www.bitfalls.com) develop websites quickly and safely, so I thought I'd share it with the world. For more information on it and its authors, head over to the home page and read up.

####Common
This is the _common_ library, meaning it's standalone and has some helpful classes that can be used in everyday coding without needing to conform to the rules of the rest of FlyingPiranhas. The classes are more or less commonly used components and utilities that appear in one form or another in every website of mid to high level complexity. There's database adapters, caching, user authentication, benchmarking tools and much more, and it's all autoloaded as needed so next to no overhead in all this. The classes are designed to leave a truly minimal footprint on your application, so give them a go.

####Requirements
- Php 5.4+
- Curl for Validator::urlExists [optional]
- APC for ApcCache [optional]

####Installation
You can install the common library with composer. Just look for flyingpiranhas/common on packagist.org and add it to your composer.json file for a painless installation. You can also download a zip right here and just point a regular PSR autoloader at the folder, but composer is the preferred method since it allows us to easily update the library and fix bugs and loopholes people help us discover further down the road.

####License
See LICENSE.md

####Contributing
There's a lot of @todos in the code, so feel free to take a look and submit a pull request if you fix anything. Also, we desperately need tests written. There's a few of them to serve as an example in the tests/ subfolder, so if you could contribute that'd be swell. There is only one rule: follow PSR-2 as much as possible. Use other classes as examples and keep the coding style consistent.

####Contact
We're on [Twitter](http://www.twitter.com/wireframework) and I am on [Google plus](http://www.gplus.to/Swader) and at [my website](http://www.bitfalls.com).
