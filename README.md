Sonatra Block Bundle
====================

[![Latest Version](https://img.shields.io/packagist/v/sonatra/block-bundle.svg)](https://packagist.org/packages/sonatra/block-bundle)
[![Build Status](https://img.shields.io/travis/sonatra/SonatraBlockBundle/master.svg)](https://travis-ci.org/sonatra/SonatraBlockBundle)
[![Coverage Status](https://img.shields.io/coveralls/sonatra/SonatraBlockBundle/master.svg)](https://coveralls.io/r/sonatra/SonatraBlockBundle?branch=master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/sonatra/SonatraBlockBundle/master.svg)](https://scrutinizer-ci.com/g/sonatra/SonatraBlockBundle?branch=master)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/0d1dda71-3f47-4dad-be53-017da27d21a8.svg)](https://insight.sensiolabs.com/projects/0d1dda71-3f47-4dad-be53-017da27d21a8)

The Sonatra BlockBundle provides tools for defining UI blocks, rendering and mapping
response data to related models. This bundle can easily create user interface elements,
and render the fields of an object in the locale/currency desired. It was intended as a
basis for designing elements of user interface, such as, panel, navbar, sidebar, data
table/grid, etc...

It was largely inspired by the [Symfony Form](https://github.com/symfony/form) in all
the features available in this bundle.

Features include:

- Block Registry
- Block Factory
- Block Type Builder
- Block Type Configuration
- Block Type Extension
- Data Mapper
- Data Transformer
- All same default Form Types
- Automatic type detection
- Templating Engine
- Block Renderer
- Twig Renderer Engine
- Twig Extension
- Block Theme and hierarchy for search parent block
- Doctrine Entity Type
- Twig filter formatter for convert the value in the specific block type
- Profiler
- Auto registration of all blocks for create the twig tags and functions (Domain Specific Language)

Documentation
-------------

The bulk of the documentation is stored in the `Resources/doc/index.md`
file in this bundle:

[Read the Documentation](Resources/doc/index.md)

Installation
------------

All the installation instructions are located in [documentation](Resources/doc/index.md).

License
-------

This bundle is under the MIT license. See the complete license in the bundle:

[Resources/meta/LICENSE](Resources/meta/LICENSE)

About
-----

Sonatra BlockBundle is a [sonatra](https://github.com/sonatra) initiative.
See also the list of [contributors](https://github.com/sonatra/SonatraBlockBundle/contributors).

Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker](https://github.com/sonatra/SonatraBlockBundle/issues).

When reporting a bug, it may be a good idea to reproduce it in a basic project
built using the [Symfony Standard Edition](https://github.com/symfony/symfony-standard)
to allow developers of the bundle to reproduce the issue by simply cloning it
and following some steps.
