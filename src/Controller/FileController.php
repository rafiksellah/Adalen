<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

class FileController extends AbstractController
{
    #[Route('/uploads/{type}/{filename}', name: 'app_file_download', requirements: ['type' => 'cv|motivation|actualities'], methods: ['GET'])]
    public function downloadFile(string $type, string $filename): Response
    {
        $projectDir = $this->getParameter('kernel.project_dir');
        
        // Essayer d'abord dans le dossier spécifique (nouveaux fichiers)
        $filePath = $projectDir . '/public/uploads/' . $type . '/' . $filename;
        
        // Si le fichier n'existe pas, essayer dans uploads directement (anciens fichiers)
        if (!file_exists($filePath) || !is_file($filePath)) {
            $filePath = $projectDir . '/public/uploads/' . $filename;
        }

        // Vérifier que le fichier existe et est bien un fichier
        if (!file_exists($filePath) || !is_file($filePath)) {
            throw $this->createNotFoundException('Fichier non trouvé: ' . $filename);
        }

        // Vérifier que le fichier est dans le bon répertoire (sécurité)
        $realPath = realpath($filePath);
        $uploadsDir = realpath($projectDir . '/public/uploads');
        if (!$realPath || strpos($realPath, $uploadsDir) !== 0) {
            throw $this->createAccessDeniedException('Accès non autorisé');
        }

        $response = new BinaryFileResponse($filePath);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $filename
        );

        // Définir le type MIME approprié
        $mimeType = mime_content_type($filePath);
        if ($mimeType) {
            $response->headers->set('Content-Type', $mimeType);
        }

        return $response;
    }
}
