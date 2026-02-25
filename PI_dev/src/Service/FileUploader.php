<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private string $targetDirectory,
        private SluggerInterface $slugger,
    ) {
    }

    public function upload(UploadedFile $file): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        
        // Utiliser getClientOriginalExtension() au lieu de guessExtension()
        // pour éviter le problème avec fileinfo
        $extension = $file->getClientOriginalExtension();
        
        // Si pas d'extension, essayer de la deviner depuis le nom original
        if (!$extension) {
            $extension = pathinfo($file->getClientOriginalName(), PATHINFO_EXTENSION);
        }
        
        // Valider l'extension
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            throw new \Exception('Extension de fichier non autorisée. Utilisez: ' . implode(', ', $allowedExtensions));
        }
        
        $fileName = $safeFilename.'-'.uniqid().'.'.$extension;

        try {
            $file->move($this->getTargetDirectory(), $fileName);
        } catch (FileException $e) {
            throw new \Exception('Erreur lors du téléchargement du fichier: ' . $e->getMessage());
        }

        return $fileName;
    }

    public function getTargetDirectory(): string
    {
        return $this->targetDirectory;
    }
}
