<?php

namespace App\Controller;

use App\Services\DataService;
use App\Services\TemplateService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use \Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
    /**
     * @Route("/", name="DefaultRoot")
     * @param TemplateService $templateService
     * @return Response
     */
    public function default(TemplateService $templateService)
    {
       return $this->redirectToRoute('Dashboard');
    }

    /**
     * @Route("/Dashboard", name="Dashboard")
     * @param TemplateService $templateService
     * @return Response
     */
    public function index(TemplateService $templateService)
    {
        return $this->render('index.html.twig',['upload' => false]);
    }

    /**
     * @Route("/Upload", name="UploadFile")
     * @param Request $request
     * @param DataService $dataService
     * @return Response
     * @throws Exception
     */
    public function upload(Request $request,DataService $dataService)
    {
        $file = $request->files->get('upload_file');
        if(!$file){
            return $this->render('index.html.twig',['upload' => false]);
        }
        $fileName = 'data_file.'.$file->guessExtension ();
        $file->move($this->getParameter('file_directory'),$fileName);
        $results = $dataService->verifyData($this->getParameter('file_directory').$fileName);

        return $this->render('verify-data.html.twig',[
            'upload' => true,
            'results' => $results
        ]);
    }

    /**
     * @Route("/Process", name="VerifyData")
     * @param Request $request
     * @return Response
     */
    public function process(Request $request)
    {
        return $this->render('verify-data.html.twig',['upload' => false, 'message' => "Upload failed."]);
    }


}