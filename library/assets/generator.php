<?php

i18n::load_path(__DIR__.DS.'locale', 'as');

app_generator::usage(ln('as.generator_title'), ln('as.generator_usage'));


// TODO: please, do this...
app_generator::alias('assets:cleanup', 'clean');
app_generator::alias('assets:compile', 'build');
