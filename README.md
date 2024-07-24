# Elemental taxonomy content block for Silverstripe

Provides a content element for the CMS where a Content Author can select a Taxonomy Type to display a sorted list of terms linked to that type.

### Features

+ Select a Taxonomy type in the content element
+ Choose to display all terms linked to that type
+ Sort by name or taxonomy sort order
+ Adds a 'Description' field to a Taxonomy term
+ Ships with schema.org `DefinedTermSet`
+ Choose to only show certain terms in the selected type
+ Optionally display the taxonomy type

Projects should override the templates provided and extend as required, e.g. to link or search based on a taxonomy term from within the list.

## Requirements

See [composer.json](./composer.json)

## Installation

```
composer require nswdpc/silverstripe-elemental-taxonomy
```

## License

[BSD-3-Clause](./LICENSE.md)

## Documentation

* [Documentation](./docs/en/001_index.md)

## Configuration

See [config.yml](./_config/config.yml) for module configuration values

## Maintainers

+ [dpcdigital@NSWDPC:~$](https://dpc.nsw.gov.au)

## Security

If you have found a security issue with this module, please email digital[@]dpc.nsw.gov.au in the first instance, detailing your findings.

## Bugtracker

We welcome bug reports, pull requests and feature requests on the Github Issue tracker for this project.

Please review the [code of conduct](./code-of-conduct.md) prior to opening a new issue.

## Development and contribution

If you would like to make contributions to the module please ensure you raise a pull request and discuss with the module maintainers.

Please review the [code of conduct](./code-of-conduct.md) prior to completing a pull request.
