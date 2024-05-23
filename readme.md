# Tamarack songbook contents and pdf generator

Songbook contents are defined in songs.json. This file is accessed by camptamarack.net via the Tamarack Songbook bot. The idea behind using github to store songbook information instead of a sql database is that it is easy to track changes and manage versions of the songbook. The songbook can be edited using any code editor or through the ui provided at camptamarack.net/campsite/songbook/.

pdf_generator.php provides a class to handle generating pdfs from songbook info stored in an array.

create_pdf.php demonstrates how the PDFGenerator class can be used along with the songbook info defined in songs.json to create a pdf. Can be used from command line. Type `php create_pdf.php -h` for commands.

Uses composer to install dependencies for scripts, if trying to use for first time make sure you have composer then call `composer install`.