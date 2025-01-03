<?php

namespace LLPhant\Embeddings\VectorStores\FileSystem;

use Exception;
use LLPhant\Embeddings\Distances\Distance;
use LLPhant\Embeddings\Distances\EuclideanDistanceL2;
use LLPhant\Embeddings\Document;
use LLPhant\Embeddings\DocumentStore\DocumentStore;
use LLPhant\Embeddings\VectorStores\VectorStoreBase;

class FileSystemVectorStore extends VectorStoreBase implements DocumentStore
{
    public string $filePath;

    /**
     * Create or open a vector storage in a local .json file
     *
     * @param  ?string  $filepath  Full path to the .json that stores the vector data. Pass "null" to default to a local directory.
     */
    public function __construct(?string $filepath = null, private readonly Distance $distance = new EuclideanDistanceL2())
    {
        $this->filePath = $filepath ?? getcwd().DIRECTORY_SEPARATOR.'documents-vectorStore.json';
    }

    public function addDocument(Document $document): void
    {
        $documentsPool = $this->readDocumentsFromFile();
        $documentsPool[] = $document;
        $this->saveDocumentsToFile($documentsPool);
    }

    public function addDocuments(array $documents): void
    {
        $documentsPool = $this->readDocumentsFromFile();
        $documentsPool = array_merge($documentsPool, $documents);
        $this->saveDocumentsToFile($documentsPool);
    }

    /**
     * @param  float[]  $embedding
     * @param  array<string, string|int>  $additionalArguments
     * @return Document[]
     */
    public function similaritySearch(array $embedding, int $k = 4, array $additionalArguments = []): array
    {
        $distances = [];
        $documentsPool = $this->readDocumentsFromFile();

        foreach ($documentsPool as $index => $document) {
            if ($document->embedding === null) {
                throw new Exception("Document with the following content has no embedding: {$document->content}");
            }
            $dist = $this->distance->measure($embedding, $document->embedding);
            $distances[$index] = $dist;
        }

        asort($distances); // Sort by distance (ascending).

        $topKIndices = array_slice(array_keys($distances), 0, $k, true);

        $results = [];
        foreach ($topKIndices as $index) {
            $results[] = $documentsPool[$index];
        }

        return $results;
    }

    public function getNumberOfDocuments(): int
    {
        $documentsPool = $this->readDocumentsFromFile();

        return count($documentsPool);
    }

    /**
     * @param  Document[]  $documents
     */
    private function saveDocumentsToFile(array $documents): bool
    {
        // Convert each document object to an associative array
        $data = array_map(fn (Document $document): array => [
            'content' => $document->content,
            'formattedContent' => $document->formattedContent,
            'embedding' => $document->embedding,
            'sourceType' => $document->sourceType,
            'sourceName' => $document->sourceName,
            'chunkNumber' => $document->chunkNumber,
            'hash' => $document->hash,
        ], $documents);

        // Encode the array of associative arrays as JSON
        $jsonData = json_encode($data, JSON_PRETTY_PRINT);

        // Write JSON data to the specified file
        return file_put_contents($this->filePath, $jsonData) !== false;
    }

    /**
     * @return Document[]
     */
    private function readDocumentsFromFile(): array
    {
        // Check if file exists and we can open it
        if (! is_readable($this->filePath)) {
            return [];
        }

        // Get the JSON data from the file
        $jsonData = file_get_contents($this->filePath);
        if ($jsonData === false) {
            return [];
        }

        // Decode the JSON data into an array
        $data = json_decode($jsonData, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($data)) {
            return [];
        }

        // Convert each associative array entry into a Document object
        return array_map(function (array $entry): Document {
            $document = new Document();
            $document->content = $entry['content'] ?? '';
            $document->formattedContent = $entry['formattedContent'] ?? null;
            $document->embedding = $entry['embedding'] ?? null;
            $document->sourceType = $entry['sourceType'] ?? null;
            $document->sourceName = $entry['sourceName'] ?? null;
            $document->chunkNumber = $entry['chunkNumber'] ?? 0;
            $document->hash = $entry['hash'] ?? null;

            return $document;
        }, $data);
    }

    public function fetchDocumentsByChunkRange(string $sourceType, string $sourceName, int $leftIndex, int $rightIndex): iterable
    {
        // This is a naive implementation, just to create an example of a DocumentStore
        $result = [];

        $documentsPool = $this->readDocumentsFromFile();

        foreach ($documentsPool as $document) {
            if ($document->sourceType === $sourceType && $document->sourceName === $sourceName && $document->chunkNumber >= $leftIndex && $document->chunkNumber <= $rightIndex) {
                $result[$document->chunkNumber] = $document;
            }
        }

        \ksort($result);

        return $result;
    }

    public function deleteStore(): bool
    {
        if (! is_readable($this->filePath)) {
            return false;
        }

        return \unlink($this->filePath);
    }
}
