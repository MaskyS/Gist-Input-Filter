CONTENTS OF THIS FILE
---------------------

 * Introduction
 * Installation
 * Configuration
 * Usage Instructions
 * Maintainers

INTRODUCTION
------------

Gist Input Filter is the easiest way to embed Gists in your Drupal website.
It substitutes [gist:xx] tags with the gist located at http://gist.github.com/xx.
Read more about on how to use the Gist Input Filter module below.

INSTALLATION
------------

 * Install as you would normally install a contributed Drupal module. Visit:
   https://drupal.org/docs/8/extending-drupal-8/installing-drupal-8-modules
   for further information.

CONFIGURATION
-------------

 * Head to admin/config/content/formats, and click Configure button that's next
   to the editor with which you want to use Gist Input Filter. Scroll down until
   you see the 'Enabled filters' section. You will find 'Gist filter' below it.
   Tick the checkbox, scroll down and  click 'Save Configuration'.

USAGE INSTRUCTIONS
------------------

To add a gist to a post, simply add [gist{display_method}:#####] where ##### is the
number of your gist and {display_method} is the way you want to display your gist.
Depending on the tag you used, Git Input Filter will replace:

 * [gist:xx] tags with the gist located at "http://gist.github.com/xx" and will
display it in a fully embedded format.
 * [gistcode:xx] tags with the gist located at "http://gist.github.com/xx", and
will display only the code content of your gist.
 * [gistlink:xx] tags with the "http://gist.github.com/xx" link.

You may also include a specific file within a multi-file gist with
[gist:#####:my_file]

MAINTAINERS
-----------

Current maintainers:

 * Kifah Meeran (Kifah Meeran) - https://drupal.org/user/3509455
 * Florian Latzel (fl3a) - https://www.drupal.org/u/fl3a
 * Toño de Pedro (capuleto) - https://www.drupal.org/u/capuleto

This project has been partly ported by a Google Code-In student.

 * Google Code-In is an international contest for high-school students that
   allows them to contribute to open source projects while earning cool
   prizes. See more here: http://g.co/gci
