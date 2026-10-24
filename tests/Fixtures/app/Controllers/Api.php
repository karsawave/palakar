<?php

namespace Controllers;

class Api extends \Resources\Controller
{
    public function store(): string
    {
        return 'stored without csrf';
    }

    public function inspect(): array
    {
        $rest = new \Resources\Rest();

        return [
            'method' => $rest->getRequestMethod(),
            'data' => $rest->getRequest(),
            'agent' => $rest->getClientHeaders()['User-Agent'] ?? '',
        ];
    }

    public function created(): string
    {
        $rest = new \Resources\Rest();
        $rest->setResponseHeader(201);

        return $rest->wrapResponseOutput(['id' => 5, 'path' => 'a/b']);
    }

    public function accepted(): string
    {
        (new \Resources\Response())->setStatusCode(202);

        return $this->outputJSON(['queued' => true]);
    }

    public function nothing(): string
    {
        return '';
    }

    public function upload(): array
    {
        $upload = new \Resources\Upload();
        $upload->setOption(['folderLocation' => getenv('PANADA_UPLOAD_DIR')] + ($_POST['options'] ?? []));
        $ok = $upload->now($_FILES['file'] ?? []);

        return ['ok' => $ok, 'info' => $upload->getFileInfo(), 'error' => $upload->getError()];
    }
}
