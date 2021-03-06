### Including your own Translations for Gravity PDF

Don't place any .mo / .po files in this directory. They will be overridden when the plugin is updated. 
Instead, put them in your language directory – by default this is /wp-content/languages/plugins/. 

Make sure you name your .mo files `gravity-pdf-previewer-{:wp_locale}.mo`, where {:wp_locale} is the ID found 
 in this list: https://make.wordpress.org/polyglots/teams/. 
 
For example, if you had a translation for Bengali you would name the file `gravity-pdf-previewer-bn_BD.mo`
 
The same naming convension applies to .po files.
 
### Contributing Translations
 
Feel free to email `support [at] gravitypdf [dot] com` with your .mo and .po files and we'll include them in the plugin. 
 
### What are .po / .mo files? 
 
They are special language files that WordPress uses to automatically translate the plugin from English to your language of choice. 
 
You can create these files by using a free program called Poedit. [There's a guide on WordPress.org](https://make.wordpress.org/polyglots/handbook/tools/poedit) 
 showing you the basic steps for importing a translation template (called a .pot file), translating and generating .po and .mo files.
  
Once generated, use the appropriate naming convension and save it in the language directory (see above for details).
  
This directory contains an up-to-date .pot file you can use with Poedit. 
  
  