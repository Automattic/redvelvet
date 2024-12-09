# Red Velvet: NPF <-> Gutenberg Converter Library

This project provides a WordPress plugin to convert between NPF (Tumblr's Neue Post Format) and Gutenberg blocks.

#### Installation

To install the dependencies, run the following command:

```
composer install
```

This command will download and install all required libraries and dependencies defined in `composer.json`, including PHPUnit and WordPress coding standards.

#### Requirements

- PHP 8.3.0 or higher
- JSON extension

### Usage

The project uses PHPUnit for unit testing and adheres to the WordPress coding standards. To run the available scripts, you can use the following commands:

- **Running Tests**:
  Run unit tests using PHPUnit:
  ```
  composer phpunit
  ```

- **Code Formatting**:
  Automatically fix coding standards issues:
  ```
  composer format:php
  ```

- **Linting Code**:
  Check PHP code for standards issues:
  ```
  composer lint:php
  ```

- **Updating Dependencies**:
  To update Composer dependencies:
  ```
  composer packages-update
  ```

#### Generating stats

Run this command to generate the test data, without changes it will generate 1000000 entries:

```
python scripts/generate_data.py
```

Run this command to read the JSON file and generate 2 csv files and summary in the end.

```
wp redvelvet diverse_massive_test_data.json
```

Run this command to read the CSV files and generate the graphs.

```
python scripts/generate_graphs.py
```

#### Autoloading

The project uses PSR-4 autoloading for both the core and test classes:
- Core classes: `src/` directory
- Test classes: `tests/` directory

### Contributing

1. Clone or fork the repository and install dependencies via Composer.
2. Edit the `composer.json` file to customize it to your needs.
3. Write tests for your code in the `tests` directory using the PHPUnit test framework.

To contribute to this project, feel free to create issues or submit pull requests. Check out the list of [Contributors](https://github.com/redvelvet/graphs/contributors).

### Resources

- [PHPUnit Documentation](https://phpunit.de/manual/current/en/)
- [NPF Specification](https://www.tumblr.com/docs/npf)

---

This project is maintained by the **Cupcake Labs Team**.
