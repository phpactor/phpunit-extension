PHPUnit Integration
===================

[![Build Status](https://travis-ci.org/phpactor/phpunit-extension.svg?branch=master)](https://travis-ci.org/phpactor/phpunit-extension)

PHPUnit integration for [Phpactor](https://github.com/phpactor/phpactor)

Features
--------

- [x] Type inference for `Assert::instanceOf(Foo::class, 'bar')` (for both
      member and static versions).
- [x] Generate new test classes

Installation
------------

Install Phpactor with the Phpactor binary:

```php
$ /path/to/phpactor extension:install phpactor/phpunit-extension
```

You can also install extensions from your editor. See
[extensions](https://phpactor.github.io/phpactor/extensions.html) for more
details.

Configuration and Usage
-----------------------

### Test Navigation

Phpactor allows you to jump to related files, just add the following
[configuration](https://phpactor.github.io/phpactor/configuration.html) to
jump from your standard source code to the test:

```javascript
{
    "navigator.destinations": {
        "source": "lib\/<kernel>Test.php"
        "test": "tests\/Unit\/<kernel>Test.php"
    },
}
```

See [Jump to related
file](https://phpactor.github.io/phpactor/navigation.html#jump-to-or-generate-related-file)
for more information.

### Test Auto Creation

When jumping to a file which does not exist, Phpactor can offer to create a
file for you. This plugin includes a simple `phpunit` class generator,
configure (promted) auto-creation as follows:

```javascript
{
    "navigator.autocreate": {
        "source": "default",
        "test": "phpunit"
    }
}
```

TODO
----

- [ ] RPC handlers to run PHPUnit, jump to failing tests?
