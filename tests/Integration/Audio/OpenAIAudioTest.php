<?php

declare(strict_types=1);

namespace Tests\Integration\Chat;

use LLPhant\Audio\OpenAIAudio;

it('can transcribe audio files', function () {
    $audio = new OpenAIAudio();
    // Original author of the audio file is KenKuhl, clipped by Davidzdh, CC BY-SA 3.0 via Wikimedia Commons
    $transcription = $audio->transcribe(__DIR__.'/wikipedia.ogg');
    expect($transcription->text)->toBe('Wikipedia, the free encyclopedia.')
        ->and($transcription->language)->toBe('english')
        ->and($transcription->durationInSeconds)->toBeBetween(2.45, 2.48);
});

it('can translate audio files', function () {
    $audio = new OpenAIAudio();
    // Original author of the audio file is KenKuhl, clipped by Davidzdh, CC BY-SA 3.0 via Wikimedia Commons
    $translation = $audio->translate(__DIR__.'/French_Can_I_have_a_table_for_two_please.wav');
    expect($translation)->toBe('Can I have a table for two, please?');
});

it('can translate audio files using a prompt', function () {
    $audio = new OpenAIAudio();
    // Original author of the audio file is KenKuhl, clipped by Davidzdh, CC BY-SA 3.0 via Wikimedia Commons
    $translation = $audio->translate(__DIR__.'/French_Can_I_have_a_table_for_two_please.wav', 'This audio is in French');
    expect($translation)->toBe('Can I have a table for two, please?');
});
