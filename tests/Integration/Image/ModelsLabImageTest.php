<?php

namespace Tests\Integration\Image;

use LLPhant\Image\ModelsLabImage;

it('can generate some stuff', function () {
// Reads MODELSLAB_API_KEY from environment
    $image = new ModelsLabImage();


// Configure model and generation params
    $image->model = 'flux';               // default: 'flux'
    $image->width = 1024;                 // default: 1024
    $image->height = 1024;
    $image->numInferenceSteps = 30;
    $image->guidanceScale = 7.5;
    $image->negativePrompt = 'blurry, low quality';

    $resultImage = $image->generateImage('A cozy cabin in the woods at dusk, watercolor style');

    expect($resultImage->url)->toStartWith('https://x.y.com');

});
