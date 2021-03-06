Feature: Specify formatters on the command line
    In order to get the output looking the way I want
    As a developer
    I need to specify the formatter

    Scenario: Specify a formatter on the command line
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
                it("fails", function() { fail(); });
            });
            """
        When I run `dspec -f summary TestSpec.php`
        Then the output should contain "1 of 2 examples failed"
        And the output should not contain ".."
        And the output should not contain "Failures:"

    Scenario: Specify multiple formatters on the command line
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
                it("fails", function() { fail(); });
            });
            """
        When I run `dspec -f summary -f failureTree TestSpec.php `
        Then the output should contain "1 of 2 examples failed"
        And the output should contain "Failures:"
        And the output should not contain ".."

    Scenario: Specify an output file
        Given a file named "TestSpec.php" with:
            """
            <?php

            describe("dave", function() {
                it("passes", function() {});
                it("fails", function() { fail(); });
            });
            """
        When I run `dspec -f summary:out.log TestSpec.php`
        Then the output should not contain "1 of 2 examples failed"
        And the file "out.log" should contain "1 of 2 examples failed"
                    
