<?php

declare(strict_types=1);

class Scraper
{
    private const int REQUEST_TIMEOUT = 1;

    public function scrape(array $urls): void
    {
        foreach ($urls as $threadUrl) {
            [$boardName, $threadId] = $this->getThreadInformation($threadUrl);
            $data = $this->sendThreadRequest($boardName, $threadId);
            sleep(self::REQUEST_TIMEOUT);

            $directoryPath = $this->getDirectoryPath($boardName, $threadId, $data->posts[0]->sub);

            foreach ($data->posts as $post) {
                if (empty($post->tim) || empty($post->ext)) {
                    return;
                }

                $imageName = $post->tim . $post->ext;
                echo "Sending request for $imageName...\n";

                $response = $this->sendRequest("https://i.4cdn.org/$boardName/$imageName");
                file_put_contents("$directoryPath/$imageName", $response, FILE_APPEND);

                echo "$imageName was successfully saved.\n";
                sleep(self::REQUEST_TIMEOUT);
            }
        }
    }

    private function sendRequest(string $url): string
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);

        if (curl_error($curl)) {
            throw new RuntimeException(curl_error($curl));
        }

        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($status !== 200) {
            throw new LogicException('Status is not successful: ' . $status);
        }

        return $response;
    }

    private function sendThreadRequest(string $boardName, string $threadId): object
    {
        $url = "https://a.4cdn.org/$boardName/thread/$threadId.json";

        try {
            $response = $this->sendRequest($url);
            return json_decode($response, flags: JSON_THROW_ON_ERROR);
        } catch (RuntimeException|LogicException|JsonException $e) {
            echo $e->getMessage();
            die(1);
        }
    }

    private function getDirectoryPath(string $boardName, string $threadId, string $threadName): string
    {
        $threadDirectoryPath = __DIR__ . "/data/$boardName";
        if (!file_exists($threadDirectoryPath)) {
            mkdir($threadDirectoryPath);
        }

        $threadName = preg_replace('/[^a-zA-Z0-9 -]/', '', $threadName);
        $directoryPath = "$threadDirectoryPath/$threadId - $threadName";
        if (!file_exists($directoryPath)) {
            mkdir($directoryPath);
        }

        return $directoryPath;
    }

    private function getThreadInformation(string $threadUrl): array
    {
        if (preg_match('#https://boards\.4chan\.org/([a-z]+)/thread/(\d*)#u', $threadUrl, $matches)) {
            $boardName = $matches[1];
            $threadId = $matches[2];

            echo "Board Name: $boardName\nThread ID: $threadId\n";

            return [$boardName, $threadId];
        } else {
            throw new RuntimeException('Incorrect URL');
        }
    }
}
