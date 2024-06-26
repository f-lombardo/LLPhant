<?php

declare(strict_types=1);

namespace Tests\Integration\Embeddings\EmbeddingGenerator;

use LLPhant\Embeddings\EmbeddingGenerator\Mistral\MistralEmbeddingGenerator;

it('can embed some stuff', function () {
    $llm = new MistralEmbeddingGenerator();
    $embedding = $llm->embedText('I love food');
    expect($embedding[0])->toBeFloat();
});

it('can embed some stuff at a high pace', function () {
    $llm = new MistralEmbeddingGenerator();
    for($i=0; $i<=5; $i++) {
        $lastEmbedding = $llm->embedText('This number is '.$i);
    }
    expect($lastEmbedding[0])->toBeFloat();
});
