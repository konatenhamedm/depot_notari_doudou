<?php

namespace App\Controller;

use App\Entity\DocumentClient;
use App\Entity\Dossier;
use App\Entity\DossierWorkflow;
use App\Entity\Enregistrement;
use App\Entity\Identification;
use App\Entity\InfoClassification;
use App\Entity\Obtention;
use App\Entity\PaiementFrais;
use App\Entity\Piece;
use App\Entity\PieceVendeur;
use App\Entity\Redaction;
use App\Entity\Remise;
use App\Entity\RemiseActe;
use App\Entity\SuiviDossierWorkflow;
use App\Form\DossierType;
use App\Repository\DocumentSigneRepository;
use App\Repository\DocumentTypeActeRepository;
use App\Repository\DossierRepository;
use App\Repository\CourierArriveRepository;
use App\Repository\DossierWorkflowRepository;
use App\Repository\FichierRepository;
use App\Repository\IdentificationRepository;
use App\Repository\PieceRepository;
use App\Repository\TypeRepository;
use App\Repository\WorkflowRepository;
use App\Service\ActionRender;
use App\Service\FormError;
use App\Service\Omines\Adapter\ArrayAdapter;
use App\Service\UploaderHelper;
use App\Service\Utils;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\TextColumn;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\WorkflowInterface;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Symfony\Component\Validator\Constraints\Language;

/**
 * @Route("/admin")
 */
class DossierController extends AbstractController
{
    use FileTrait;

    private $dossierWorkflow;

    const TAB_ID = 'smartwizard-3';

    private const FILE_PATH = 'acte_vente';

    public function __construct(WorkflowInterface $dossierWorkflow)
    {
        $this->dossierWorkflow = $dossierWorkflow;
    }

    #[Route('/{id}/print-cr', name: 'app_reunion_print_cr', methods: ['DELETE', 'GET'])]
    public function printAction(Request $request, Dossier $dossier, IdentificationRepository $identificationRepository)
    {

        $language = new \PhpOffice\PhpWord\Style\Language(\PhpOffice\PhpWord\Style\Language::FR_FR);
        $phpWord  = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->getSettings()->setThemeFontLang($language);

        $phpWord->setDefaultFontName('Arial Narrow');

        $section = $phpWord->addSection([
            'orientation' => 'portrait',
        ]);

        $phpWord->addParagraphStyle('pJustify', array('align' => 'both', 'spaceBefore' => 3, 'spaceAfter' => 3, 'spacing' => 3));

        /* Utils::wordHeader($phpWord, $this->getParameter('assets_dir'), $section, 'landscape', null, 'first');
        Utils::wordFooter($section, 'landscape');


        $textBox = $section->addTextBox([
            'alignment'   => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceAfter' => 0
            //, 'positioning' => 'absolute'

            , 'width' => 700,
            'height'      => 1,
            'borderSize'  => 2,
            'borderColor' => '#cf2e2e',
            //'borderStyle' => 'double',
            //'wrap' => 'square'
        ]); */



        $fontSize   = 7;
        $bold       = ['bold' => true, 'size' => $fontSize];
        $center     = ['align' => 'center', 'spaceAfter' => 0];
        $numRapport = 10 + 1;
        $padSize    = str_pad($numRapport, 3, '0', STR_PAD_LEFT);

        /* $section->addTextBreak(1.2); */
        $section->addText('BORDEREAU des ACTES DEPOSES
        COCODY
        01 JANVIER 2023
        ', ['bold' => true, 'size' => 14, 'underline' => 'single'], ['align' => 'center']);

        $box = $section->addTextBox([
            'alignment'   => \PhpOffice\PhpWord\SimpleType\Jc::START,  'width' => 450,
            'height'      => 50,
            'borderSize'  => 2,
            'borderColor' => 'black',
            //'borderStyle' => 'double',
            //'wrap' => 'square'
        ]);
        $box->addText('(1) N°  du compte                                                                                                  Bordereau……….……………                                                  
        ',);
        $box->addText('Enreg le……..……………..…                                                                                 Vol……..f..°……...n°…………',);


        /* $section->addTextBox([
            'alignment'   => \PhpOffice\PhpWord\SimpleType\Jc::END,  'width' => 100,
            'height'      => 100,
            'borderSize'  => 2,
            'borderColor' => 'black',
            //'borderStyle' => 'double',
            //'wrap' => 'square'
        ]); */
        /* $section->addTextBreak(1); */






        $noSpace         = ['spaceAfter' => 0];
        $styleTable      = ['borderSize' => 6, 'borderColor' => '000000', 'cellPadding' => 40, 'cellMargin' => 40];
        $cellRowSpan     = ['vMerge' => 'restart', 'valign' => 'center'];
        $cellRowSpanC     = ['vMerge' => 'continue', 'valign' => 'center'];
        $cellRowContinue = ['vMerge' => 'continue'];
        $cellColSpan     = ['gridSpan' => 2, 'valign' => 'center'];
        $cellHCentered   = ['align' => 'center'];
        $cellVCentered   = ['valign' => 'center'];
        $cellHRight      = ['align' => 'right'];

        $cellHCenteredNoSpace = array_merge($cellHCentered, $noSpace);

        $phpWord->addTableStyle('Colspan Rowspan', $styleTable);

        $w1             = 3500;
        $w2             = 1500;
        $noBorderBottom = ['borderBottomSize' => 0, 'borderBottomColor' => 'FFFFFF'];
        $noBorderTop    = ['borderTopSize' => 0, 'borderTopColor' => 'FFFFFF'];
        $noBorderLeft   = ['borderLeftSize' => 0, 'borderLeftColor' => 'FFFFFF'];
        $noBorderRight  = ['borderRightSize' => 0, 'borderRightColor' => 'FFFFFF'];

        $cellVCenteredBg = array_merge($cellVCentered, ['bgColor' => 'white', 'color' => 'FFFFFF', 'valign' => 'center', 'spaceAfter' => 0]);

        //c5e0b3

        //$section->addPageBreak();


        /*   $section = $phpWord->addSection([
            'orientation' => 'landscape',
        ]); */

        /* $bgColor = '#cf2e2e'; */
        $bgColor = '#ffff';

        $table = $section->addTable('Colspan Rowspan');


        $w2 = 5000;
        $w3 = 3000;
        $w4 = 25;
        $w5 = 1500;


        $cellRowSpan2 = array('vMerge' => 'restart');
        $cellRowContinue2 = array('vMerge' => 'continue');
        $cellColSpan2 = array('gridSpan' => 2);
        $cellColSpan3 = array('gridSpan' => 3);
        $cellColSpan6 = array('gridSpan' => 6);

        $cellVCenteredBg['bgColor'] = $bgColor;
        $table->addRow(null, ['cantSplit' => true]);
        $cell    = $table->addCell($w4, $cellVCenteredBg);
        $textRun = $cell->addTextRun($cellHCenteredNoSpace);
        $textRun->addText('N° D’ORDRE', ['bold' => false, 'allCaps' => true, 'color' => 'black'], ['align' => 'center']);
        $cell    = $table->addCell($w3, $cellVCenteredBg);
        $textRun = $cell->addTextRun($cellHCenteredNoSpace);
        $textRun->addText('DATE DE L’ACTE
et indication du nombre des rôles, mots et chiffres nuls
', ['bold' => false, 'allCaps' => true, 'color' => 'black'], ['align' => 'center']);
        $cell    = $table->addCell($w2, $cellColSpan2);
        $textRun = $cell->addTextRun($cellHCenteredNoSpace);
        $textRun->addText('NATURE    DE    L’ACTE
        Et noms des parties 
        ', ['bold' => false, 'allCaps' => true, 'color' => 'black'], ['align' => 'center']);
        $cell    = $table->addCell($w3, $cellVCenteredBg);
        $textRun = $cell->addTextRun($cellHCenteredNoSpace);
        $textRun->addText('MONTANT
        Des
        droits perçus
        ', ['bold' => false, 'allCaps' => true, 'color' => 'black'], ['align' => 'center']);
        $cell    = $table->addCell($w5, $cellVCenteredBg);
        $textRun = $cell->addTextRun($cellHCenteredNoSpace);
        $textRun->addText('NUMERO', ['bold' => false, 'allCaps' => true, 'color' => 'black'], ['align' => 'center']);

        /* NOUVELLE LIGNE */

        $table->addRow(null, ['cantSplit' => true, 'align' => 'center']);
        $table->addCell($w4, $cellRowSpan2)->addTextRun($cellHCenteredNoSpace)->addText("1", ['align' => 'center']);
        $table->addCell($w3, $cellRowSpan2)->addTextRun($cellHCenteredNoSpace)->addText("Date de l’acte : 
        31 décembre 2022 et Janvier 2023
        ………4 Rôles ½ 
        …00…/ …Renvois
        …00…/..Lignes nulles
        ……00/   Mots nuls
        …00…/…Chiffres
        ", ['align' => 'center']);
        $table->addCell($w2, $cellColSpan2)->addTextRun($cellHCenteredNoSpace)->addText("VENTE PAR MOSNIEUR " . $identificationRepository->findOneBy(array('dossier' => $dossier->getId()))->getAcheteur()->getNom() . ' ' . $identificationRepository->findOneBy(array('dossier' => $dossier->getId()))->getAcheteur()->getPrenom() . "AU PROFIT DE MONSIEUR" . $identificationRepository->findOneBy(array('dossier' => $dossier->getId()))->getVendeur()->getNom() . ' ' . $identificationRepository->findOneBy(array('dossier' => $dossier->getId()))->getVendeur()->getPrenom(), ['bold' => true, 'allCaps' => true, 'color' => 'black'],);
        $table->addCell($w3, $cellRowContinue2)->addText("");
        $table->addCell($w5, $cellRowSpan2)->addText("");

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell($w4, $cellRowContinue2);
        $table->addCell($w3, $cellRowContinue2);
        $table->addCell($w2, $cellColSpan2, ['borderSize' => 0, 'borderColor' => false])->addTextRun($cellHCenteredNoSpace)->addText("Lot 01 – Ilot 02                                                                     

        Titre Foncier 03 Cocody
        ", ['bold' => true, 'allCaps' => true, 'color' => 'black']);

        $table->addCell($w3, $cellRowContinue2)->addText("");
        $table->addCell($w5, $cellRowContinue2);

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell($w4, $cellRowContinue2);
        $table->addCell($w3, $cellRowContinue2);
        $table->addCell($w2, $cellColSpan2)->addTextRun($cellHCenteredNoSpace)->addText("REP: 1234                                                              DF", ['bold' => true, 'allCaps' => true, 'color' => 'black'],);
        $table->addCell($w3)->addTextRun($cellHCenteredNoSpace)->addText("18.000 FCFA", ['bold' => true, 'allCaps' => true, 'color' => 'black'],);
        $table->addCell($w5, $cellRowContinue2);

        /* Nouvelle ligne */
        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell($w4, $cellVCenteredBg)->addTextRun($cellHCenteredNoSpace)->addText("2", ['align' => 'center']);
        $table->addCell($w3, $cellVCenteredBg)->addTextRun($cellHCenteredNoSpace)->addText("Date de l’acte : 
        ………0 Rôle
        …00…/ …Renvois
        …00…/..Lignes nulles
        ……00/   Mots nuls
        …00…/…Chiffres
        ", ['align' => 'center']);
        $table->addCell($w2, $cellColSpan2)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w5, $cellVCenteredBg)->addText("");



        /* NOUVELLE LIGNE */

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell($w4, $cellVCenteredBg)->addTextRun($cellHCenteredNoSpace)->addText("3");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w2, $cellColSpan2)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w5, $cellVCenteredBg)->addText("");

        /* NOUVELLE LIGNE */

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell(null, $cellColSpan3)->addTextRun($cellHCenteredNoSpace)->addText("Le   présent   bordereau   contenant    UN     acte 
         Numéroté de   1   à   1   est  certifié  exact  et  complet À     
         Abidjan, le  01 Janvier 2023.                                                                                          
         Signature du notaire                                
         ");
        $table->addCell(null, $cellColSpan3)->addTextRun($cellHCenteredNoSpace)->addText("Arrêt le présent bordereau à la somme  
        
        (en toutes lettres)                                                         
        Cachet du bureau :
        ");

        /* NOUVELLE LIGNE */

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell(null, $cellColSpan6)->addTextRun($cellHCenteredNoSpace)->addText("                             CADRE   RESERVE   A   L ’ADMINISTRATION   (Dépouillement des droits perçus)                               
         ", ['align' => 'center']);
        /*             NOUEVLLE LIGNE */

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell(null, $cellColSpan2)->addTextRun($cellHCenteredNoSpace)->addText("NATURE", $cellHCenteredNoSpace);
        $table->addCell($w3, $cellVCenteredBg)->addTextRun($cellHCenteredNoSpace)->addText("NOMBRE");
        $table->addCell($w2, $cellVCenteredBg)->addTextRun($cellHCenteredNoSpace)->addText("MONTANT");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addTextRun($cellHCenteredNoSpace)->addText("OBSERVATION");

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell(null, $cellColSpan2)->addText("", $cellHCenteredNoSpace);
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w2, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell(null, $cellColSpan2)->addText("", $cellHCenteredNoSpace);
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w2, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");

        $table->addRow(null, ['cantSplit' => true]);
        $table->addCell(null, $cellColSpan2)->addText("", $cellHCenteredNoSpace);
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w2, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");
        $table->addCell($w3, $cellVCenteredBg)->addText("");




        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $fileName  = $this->getUploadDir('reunions/rapports', true) . '/rapport_reunion_' . 'rrr' . '.docx';
        $objWriter->save($fileName);

        return $this->file($fileName);
    }






    /**
     * @Route("/dossier/acteVente", name="dossierActeVente")
     * @param DossierRepository $repository
     * @return Response
     */
    public function index(Request $request): Response
    {
        $etats = [
            'cree' => 'En cours de traitement',
            'termine' => 'Finalisés',
            'archive' => 'Archivés'
        ];
        return $this->render('_admin/dossier/index.html.twig', ['etats' => $etats, 'titre' => 'Liste des actes de vente']);
    }


    /**
     * @Route("/dossier/{etat}/liste", name="acte_vente_liste")
     * @param DossierRepository $repository
     * @return Response
     */
    public function liste(
        Request $request,
        string $etat,
        DataTableFactory $dataTableFactory,
        DossierRepository $dossierRepository,
        WorkflowRepository $workflowRepository
    ): Response {



        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $dossierRepository->countAll($etat);
        $totalFilteredData = $dossierRepository->countAll($etat, $searchValue);
        $data = $dossierRepository->getAll($etat, $limit, $offset,  $searchValue);
        //dd($data);



        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ])
            ->setName('dt_' . $etat);


        $table->add('numero_ouverture', TextColumn::class, ['label' => 'Numéro ouverture', 'className' => 'w-100px'])
            ->add('numero_repertoire', TextColumn::class, ['label' => 'Numéro répertoire', 'className' => 'w-100px'])
            ->add('date_creation', DateTimeColumn::class, ['label' => 'Date de création', 'format' => 'd-m-Y'])
            ->add('objet', TextColumn::class, ['label' => 'Objet', 'className' => 'w-100px text-right'])
            ->add('type_acte_id', NumberColumn::class, ['visible' => false])
            ->add('etape', TextColumn::class, ['label' => 'Etape', 'render' => function ($value, $context) use ($workflowRepository) {
                $current = $workflowRepository->findOneBy(['typeActe' => $context['type_acte_id'], 'route' => $context['etape']]);

                if ($current) {
                    return $current->getLibelleEtape();
                }
                return 'Non Entamé';
            }]);


        $renders = [
            'edit' =>  new ActionRender(function () use ($etat) {
                return true;
            }),
            'suivi' =>  new ActionRender(function () use ($etat) {
                return true;
            }),
            'delete' => new ActionRender(function () use ($etat) {
                return $etat == 'cree';
            }),
            'archive' => new ActionRender(function () use ($etat) {
                return $etat == 'termine';
            }),
            'details' => new ActionRender(function () use ($etat) {
                return true;
            }),
        ];


        $hasActions = false;

        foreach ($renders as $_ => $cb) {
            if ($cb->execute()) {
                $hasActions = true;
                break;
            }
        }


        if ($hasActions) {
            $table->add('id', TextColumn::class, [
                'label' => 'Actions', 'field' => 'id', 'orderable' => false, 'globalSearchable' => false, 'className' => 'grid_row_actions', 'render' => function ($value, $context) use ($renders, $etat) {

                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',

                        'actions' => [
                            /*  'edit' => [
                                'url' => $this->generateUrl('app_reunion_print_cr', ['id' => $value]), 'ajax' => false,
                                'stacked' => false, 'icon' => '%icon% fe fe-edit', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']

                            ], */
                            'edit' => [
                                'url' => $this->generateUrl('dossierActeVente_edit', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-edit', 'attrs' => ['class' => 'btn-success'], 'render' => $renders['edit']

                            ],
                            'suivi' => [
                                'url' => $this->generateUrl('dossier_suivi', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-book', 'attrs' => ['class' => 'btn-dark', 'title' => 'Suivi du dossier'], 'render' => $renders['suivi']

                            ],
                            'details' => [
                                'url' => $this->generateUrl('dossierActeVente_show', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-eye', 'attrs' => ['class' => 'btn-primary'], 'render' => $renders['details']

                            ],
                            'delete' => [
                                'url' => $this->generateUrl('dossierActeVente_delete', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-trash-2', 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression'],  'render' => $renders['delete']

                            ],
                            'archive' => [
                                'url' => $this->generateUrl('dossierActeVente_archive', ['id' => $value]), 'ajax' => true, 'icon' => '%icon% fe fe-folder', 'attrs' => ['class' => 'btn-info', 'title' => 'Archivage'],  'render' => $renders['archive']

                            ],
                        ]
                    ];
                    return $this->renderView('_includes/default_actions.html.twig', compact('options', 'context'));
                }
            ]);
        }


        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getResponse();
        }

        return $this->render('_admin/dossier/liste.html.twig', ['datatable' => $table, 'etat' => $etat]);
    }

    /**
     * @Route("/dossier/{id}/show", name="dossierActeVente_show", methods={"GET"})
     * @param dossier $dossier
     * @param $id
     * @param DossierRepository $repository
     * @return Response
     */
    public function show(dossier $dossier, $id, DossierRepository $repository, DossierWorkflowRepository $dossierWorkflowRepository): Response
    {
        $form = $this->createForm(DossierType::class, $dossier, [

            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_show', [
                'id' => $dossier->getId(),
            ])
        ]);

        return $this->render('_admin/dossier/voir.html.twig', [
            'titre' => 'Acte de vente',
            'workflow' => $dossierWorkflowRepository->getListe($dossier->getId()),
            /* 'data'=>$repository->getFichier($id),*/
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }



    /**
     * @Route("/dossier/{id}/details", name="dossierActeVente_details", methods={"GET","POST"})
     * @param DossierWorkflowRepository $dossierWorkflowRepository
     * @param PieceRepository $pieceRepository
     * @param DocumentSigneRepository $documentSigneRepository
     * @param IdentificationRepository $identificationRepository
     * @param Request $request
     * @param Dossier $dossier
     * @param EntityManagerInterface $em
     * @param $id
     * @param DossierRepository $repository
     * @return Response
     */
    public function details(DossierRepository $repository, DossierWorkflowRepository $dossierWorkflowRepository, PieceRepository $pieceRepository, DocumentSigneRepository $documentSigneRepository, IdentificationRepository $identificationRepository, Request $request, Dossier $dossier, EntityManagerInterface $em, $id): Response
    {

        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_details', [
                'id' => $dossier->getId(),
            ])
        ]);
        $form->handleRequest($request);

        $isAjax = $request->isXmlHttpRequest();

        // $type = $form->getData()->getType();
        if ($form->isSubmitted()) {
            //dd($isAjax);
            $redirect = $this->generateUrl('dossierActeVente');
            $brochureFile = $form->get('documentSignes')->getData();
            $brochureFile2 = $form->get('pieces')->getData();
            $brochureFile3 = $form->get('enregistrements')->getData();
            $piecesVendeur = $form->get('pieceVendeurs')->getData();
            $redaction = $form->get('redactions')->getData();
            $remise = $form->get('remises')->getData();
            $obtention = $form->get('obtentions')->getData();
            $remiseActe = $form->get('remiseActes')->getData();
            $statut = 0;
            if ($form->isValid()) {

                /* $this->saveFile($brochureFile);
                $this->saveFile($piecesVendeur);
                $this->saveFile($brochureFile3);
                $this->saveFile($brochureFile2);
                $this->saveFile($redaction);
                $this->saveFile($remise);
                $this->saveFile($obtention);
                $this->saveFile($remiseActe); */


                $em->persist($dossier);
                $em->flush();

                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            }

            // if ($isAjax) {
            //return $this->json(compact('statut', 'message', 'redirect'));
            //  } else {
            if ($statut == 1) {
                return $this->redirect($redirect);
            }
            // }
        }

        return $this->render('_admin/dossier/details.html.twig', [
            'titre' => 'Acte de vente',
            'workflow' => $dossierWorkflowRepository->getListe($dossier->getId()),
            'dossier' => $dossier,
            'form' => $form->createView(),
            'identification_nombre' => $identificationRepository->getLength($dossier->getId()),
            'piece_nombre' => $pieceRepository->getLength($dossier->getId()),
            'document_nombre' => $documentSigneRepository->getLength($dossier->getId()),
        ]);
    }


    /**
     * @Route("/dossier/{id}/archive", name="dossierActeVente_archive", methods={"GET","POST"})
     */
    public function archive(Request $request,  Dossier $dossier, FormError $formError, DocumentTypeActeRepository $documentTypeActeRepository, WorkflowRepository $workflowRepository, EntityManagerInterface $em, DossierRepository $repository, TypeRepository $typeRepository)
    {
    }

    /**
     * @Route("/dossier/new", name="dossierActeVente_new", methods={"GET","POST"})
     * @param Request $request
     * @param DocumentTypeActeRepository $documentTypeActeRepository
     * @param WorkflowRepository $workflowRepository
     * @param EntityManagerInterface $em
     * @param DossierRepository $repository
     * @param TypeRepository $typeRepository
     * @return Response
     */
    public function new(Request $request, FormError $formError, DocumentTypeActeRepository $documentTypeActeRepository, WorkflowRepository $workflowRepository, EntityManagerInterface $em, DossierRepository $repository, TypeRepository $typeRepository): Response
    {
        $dossier = new Dossier();
        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_new')
        ]);

        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {
            $statut = 1;
            $acteVente = $typeRepository->findOneBy(['code' => 'acte_vente']);
            $workflows = $workflowRepository->getFichier($acteVente->getId());
            $listeDocument = $documentTypeActeRepository->getListeDocument();

            $redirect = $this->generateUrl('dossierActeVente');
            $date = (new \DateTime('now'))->format('Y-m-d');



            if ($form->isValid()) {

                $currentDate = new \DateTime();
                foreach ($workflows as $workflow) {

                    $dossierWorkflow = new DossierWorkflow();
                    $nbre = $workflow->getNombreJours();
                    $dossierWorkflow->setDossier($dossier)
                        ->setWorkflow($workflow)
                        ->setDateDebut($currentDate);

                    $currentDate->modify("+{$nbre} day");
                    $dossierWorkflow->setDateFin($currentDate);

                    $dossier->addDossierWorkflow($dossierWorkflow);
                }
                $this->dossierWorkflow->getMarking($dossier);

                $dossier->setTypeActe($acteVente);
                $dossier->setEtape('');
                $em->persist($dossier);
                $em->flush();
                $data = true;
                $message = 'Opération effectuée avec succès';

                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/dossier/new.html.twig', [
            'titre' => 'Acte de vente',
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/dossier/{id}/edit", name="dossierActeVente_edit", methods={"GET","POST"})
     * @param Request $request
     * @param Dossier $dossier
     * @param EntityManagerInterface $em
     * @param $id
     * @param DossierRepository $repository
     * @return Response
     */
    public function edit(Request $request, Dossier $dossier, FormError $formError, EntityManagerInterface $em, $id, DossierRepository $repository, WorkflowRepository $workflowRepository): Response
    {

        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'action' => $this->generateUrl('dossierActeVente_edit', [
                'id' => $dossier->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();
        if ($form->isSubmitted()) {

            $redirect = $this->generateUrl('dossierActeVente');


            if ($form->isValid()) {

                $currentDate = new \DateTimeImmutable();
                $currentDate->setTime(0, 0);
                $acteVente = $dossier->getTypeActe();
                $workflows = $workflowRepository->getFichier($acteVente->getId());
                $dossierWorkflowRepository = $em->getRepository(DossierWorkflow::class);
                foreach ($workflows as $workflow) {
                    $nbre = $workflow->getNombreJours();
                    if (!$dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $workflow])) {
                        $dossierWorkflow = new DossierWorkflow();
                        $dossierWorkflow->setDossier($dossier);

                        $dossierWorkflow->setDateDebut($currentDate);
                        $dateFin = $currentDate->modify("+{$nbre} day");
                    } else {
                        $dt = clone $dossierWorkflow->getDateDebut();
                        $dateFin = $dt->modify("+{$nbre} day");
                    }




                    $dossierWorkflow->setWorkflow($workflow)

                        ->setDateFin($dateFin);

                    $dossierWorkflow->setWorkflow($workflow)
                        ->setDateDebut($currentDate)
                        ->setDateFin($dateFin);

                    $dossier->addDossierWorkflow($dossierWorkflow);
                }
                $em->persist($dossier);
                $em->flush();
                $data = true;
                $message = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }

            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/dossier/edit.html.twig', [
            'titre' => 'Acte de vente',
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/dossier/delete/{id}", name="dossierActeVente_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param dossier $dossier
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em, dossier $dossier): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'dossierActeVente_delete',
                    [
                        'id' => $dossier->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($dossier);
            $em->flush();

            $redirect = $this->generateUrl('dossierActeVente');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut' => 1,
                'message' => $message,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }
        }
        return $this->render('_admin/dossier/delete.html.twig', [
            'dossier' => $dossier,
            'form' => $form->createView(),
        ]);
    }


    /**
     * @Route("/dossier/valider", name="valider", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param DossierRepository $repository
     * @return Response
     */
    public function valider(Request $request, EntityManagerInterface $entityManager, DossierRepository $repository): Response
    {
        $response = new Response();
        $etape = "";
        //  dd(($request->get('vendeur')));
        if ($request->isXmlHttpRequest()) { // pour vérifier la présence d'une requete Ajax
            // dd($request->get('id'),$request->get('etape'));
            $id = "";
            $id = $request->get('id');
            $etape = $request->get('etape');
            // dd();
            if ($id) {
                //dd($id);
                //dd("==================",$id);
                // $ensembles = $repository->listeDepartement($id);
                $dossier = $repository->find($id);
                // dd($dossier);
                if ($etape == 1) {
                    $dossier->setEtape("Recueil des pièces");
                } elseif ($etape == 2) {
                    $dossier->setEtape("Redaction");
                } elseif ($etape == 3) {
                    $dossier->setEtape("Signature");
                } elseif ($etape == 4) {
                    $dossier->setEtape("Enregistrement");
                } elseif ($etape == 5) {
                    $dossier->setEtape("Acte");
                } elseif ($etape == 6) {
                    $dossier->setEtape("Obtention");
                } elseif ($etape == 7) {
                    $dossier->setEtape("Remise");
                } elseif ($etape == 8) {
                    $dossier->setEtape("Classification");
                } elseif ($etape == 9) {
                    $dossier->setEtape("Archive");
                    $dossier->setEtat(1);
                }


                $entityManager->persist($dossier);
                $entityManager->flush();
                $data = $this->json([
                    'status' => $etape,
                ]);

                //$data = json_encode($arrayCollection); // formater le résultat de la requête en json
                //dd($data);
                $response->headers->set('Content-Type', 'application/json');
                $response->setContent($data);
            }
        }

        return $this->json([
            'code' => 200,
            'message' => 'ça marche bien',
            'status' => $etape,
        ], 200);
    }

    /**
     * @Route("/dossier/{id}/confirmation", name="dossierActeVente_confirmation", methods={"GET"})
     * @param $id
     * @param Dossier $parent
     * @return Response
     */
    public function confirmation($id, Dossier $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig', [
            'id' => $id,
            'action' => 'dossierActeVente',
        ]);
    }


    /**
     * @Route("/dossier/{id}/active", name="dossierActeVente_active", methods={"GET"})
     * @param $id
     * @param Dossier $parent
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id, Dossier $parent, EntityManagerInterface $entityManager): Response
    {

        if ($parent->getActive() == 1) {

            $parent->setActive(0);
        } else {

            $parent->setActive(1);
        }

        $entityManager->persist($parent);
        $entityManager->flush();
        $redirect = $this->generateUrl('dossierActeVente');
        return $this->redirect($redirect);
    }

    /**
     * @Route("/dossier/{id}/suivi", name="dossier_suivi", methods={"GET", "POST"})
     *
     */
    public function suivi(Request $request, Dossier $dossier, WorkflowRepository $workflowRepository)
    {
        $typeActe = $dossier->getTypeActe();
        $etapes = $workflowRepository->findBy(['active' => 1, 'typeActe' => $typeActe], ['numeroEtape' => 'asc']);
        //dd($etapes);
        return $this->render('_admin/dossier/suivi.html.twig', [
            'dossier' => $dossier,
            'base_url' => $this->generateUrl('dossierActeVente'),
            'type_acte' => $typeActe,
            'etapes' => $etapes
        ]);
    }

    /**
     * @Route("/dossier/{id}/receuil-piece", name="acte_vente_piece", methods={"GET", "POST", "PUT"})
     *
     */
    public function piece(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository,
        FichierRepository $fichierRepository
    ) {


        $typeActe = $dossier->getTypeActe();
        //$documents =  $documentTypeActeRepository->getDocumentsEtape($typeActe, 'piece');

        if ($dossier->getMontantAcheteur() == null) {
            $dossier->setMontantAcheteur('0');
        }
        if ($dossier->getMontantVendeur() == null) {
            $dossier->setMontantVendeur('0');
        }
        $identification = $dossier->getIdentifications()->first();

        $acheteur = $identification->getAcheteur();
        $vendeur = $identification->getVendeur();

        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);
        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        $oldPieces = $dossier->getPieces();


        $docAcheteurs = $acheteur->getDocuments();
        $docVendeurs = $vendeur->getDocuments();


        foreach ($docAcheteurs as $document) {

            $hasDoc = $oldPieces->filter(function (Piece $piece) use ($document) {
                return $piece->getOrigine() == Piece::ORIGINE_ACHETEUR && $piece->getLibDocument() == $document->getLibelle() && $piece->getClient();
            })->first();



            if (!$hasDoc) {
                $fichier = $fichierRepository->find($document->getFichier()->getId());
                $piece = new Piece();
                $piece->setDocument($document->getDocument());
                $piece->setLibDocument($document->getLibelle());
                $piece->setFichier($fichier);
                $piece->setOrigine(Piece::ORIGINE_ACHETEUR);
                $dossier->addPiece($piece);
                $piece->setClient(true);
            }
        }


        foreach ($docVendeurs as $document) {
            $hasDoc = $oldPieces->filter(function (Piece $piece) use ($document) {
                return $piece->getOrigine() == Piece::ORIGINE_VENDEUR  &&
                    $piece->getLibDocument() == $document->getLibelle() &&
                    $piece->getClient();
            })->first();

            if (!$hasDoc) {
                $fichier = $fichierRepository->find($document->getFichier()->getId());
                $piece = new Piece();
                $piece->setFichier($fichier);
                $piece->setDocument($document->getDocument());
                $piece->setLibDocument($document->getLibelle());
                $piece->setOrigine(Piece::ORIGINE_VENDEUR);
                $piece->setClient(true);
                $dossier->addPiece($piece);
            }
        }





        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());

        $filePath = 'acte_vente';
        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir($filePath, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, $urlParams)
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {
                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }

                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];
                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }

                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/dossier/{id}/identification", name="acte_vente_identification", methods={"GET", "POST", "PUT"})
     *
     */
    public function identification(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getIdentifications()->count()) {
            $identification = new Identification();
            $dossier->addIdentification($identification);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'validation_groups' => ['Default', $routeWithoutPrefix],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {
                if ($this->dossierWorkflow->can($dossier, 'post_creation')) {
                    $this->dossierWorkflow->apply($dossier, 'post_creation');
                }

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dossier/{id}/redaction", name="acte_vente_redaction", methods={"GET", "POST"})
     *
     */
    public function redaction(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getRedactions()->count()) {
            $redaction = new Redaction();
            $redaction->setNumVersion(1);
            $dossier->addRedaction($redaction);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];


                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dossier/{id}/classification", name="acte_vente_classification", methods={"GET", "POST"})
     *
     */
    public function classification(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getInfoClassification()) {
            $classification = new InfoClassification();
            $dossier->setInfoClassification($classification);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => current(array_keys($dossier->getEtat())),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();

        $data = null;

        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isDone = $form->get('cloture')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                $redirect = $this->generateUrl($currentRoute, $urlParams);
                $modal = $isDone;

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }

                if ($isDone) {
                    if ($this->dossierWorkflow->can($dossier, 'cloture')) {
                        $this->dossierWorkflow->apply($dossier, 'cloture');
                    }
                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                    }
                    $suivi->setEtat(true);
                    $data = true;
                }
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();


                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dossier/{id}/signature-acte", name="acte_vente_signature", methods={"GET", "POST"})
     *
     */
    public function signature(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);



        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dossier/{id}/enregistrement-acte", name="acte_vente_enregistrement", methods={"GET", "POST"})
     *
     */
    public function enregistrement(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        $oldEnregistrements = $dossier->getEnregistrements();

        foreach (Enregistrement::SENS as $idSens => $value) {
            $hasValue = $oldEnregistrements->filter(function (Enregistrement $enregistrement) use ($idSens) {
                return $enregistrement->getSens() == $idSens;
            })->current();

            if (!$hasValue) {
                $enregistrement = new Enregistrement();
                $enregistrement->setSens(intval($idSens));
                $dossier->addEnregistrement($enregistrement);
            }
        }


        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];


                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/dossier/{id}/paiement-acte", name="acte_vente_paiement", methods={"GET", "POST"})
     *
     */
    public function paiement(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        $oldEnregistrements = $dossier->getPaiementFrais();

        foreach (PaiementFrais::Sens as $idSens => $value) {
            $hasValue = $oldEnregistrements->filter(function (PaiementFrais $enregistrement) use ($idSens) {
                return $enregistrement->getSens() == $idSens;
            })->current();

            if (!$hasValue) {
                $enregistrement = new PaiementFrais();
                $enregistrement->getSens(intval($idSens));
                $dossier->addPaiementFrai($enregistrement);
            }
        }


        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
                //'file_prefix' => str_slug('', '_')
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];


                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/dossier/{id}/titre-propriete", name="acte_vente_remise", methods={"GET", "POST"})
     *
     */
    public function remise(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getRemises()->count()) {
            $remise = new Remise();
            $dossier->addRemise($remise);
        }

        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'current_etape' => $dossier->getEtape(),
            'etape' => strtolower(__FUNCTION__),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()) {
                        $suivi->setDateFin(new \DateTime());
                        $dossier->setEtape($next['route']);
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/dossier/{id}/obtention", name="acte_vente_obtention", methods={"GET", "POST"})
     *
     */
    public function obtention(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getObtentions()->count()) {
            $obtention = new Obtention();
            $dossier->addObtention($obtention);
        }



        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(__FUNCTION__),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()) {
                        $dossier->setEtape($next['route']);
                        $suivi->setDateFin(new \DateTime());
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }



    /**
     * @Route("/dossier/{id}/remise-acte", name="acte_vente_remise_acte", methods={"GET", "POST"})
     */
    public function remiseActe(
        Request $request,
        Dossier $dossier,
        EntityManagerInterface $em,
        FormError $formError,
        WorkflowRepository $workflowRepository,
        DossierWorkflowRepository $dossierWorkflowRepository
    ) {
        $typeActe = $dossier->getTypeActe();
        $prefixe = $typeActe->getCode();
        $currentRoute = $request->attributes->get('_route');
        $routeWithoutPrefix = str_replace("{$prefixe}_", '', $currentRoute);


        $current = $workflowRepository->findOneBy(['typeActe' => $typeActe, 'route' => $routeWithoutPrefix]);

        if (!$dossier->getRemiseActes()->count()) {
            $remise = new RemiseActe();
            $dossier->addRemiseActe($remise);
        }


        $urlParams = ['id' => $dossier->getId()];


        $next = $workflowRepository->getNext($typeActe->getId(), $current->getNumeroEtape());


        $form = $this->createForm(DossierType::class, $dossier, [
            'method' => 'POST',
            'etape' => strtolower(snake_case(__FUNCTION__)),
            'current_etape' => $dossier->getEtape(),
            'doc_options' => [
                'uploadDir' => $this->getUploadDir(self::FILE_PATH, true),
                'attrs' => ['class' => 'filestyle'],
            ],
            'action' => $this->generateUrl($currentRoute, ['id' => $dossier->getId()])
        ]);
        $form->handleRequest($request);

        $data = null;
        $url = null;
        $tabId = null;
        $modal = true;

        $isAjax = $request->isXmlHttpRequest();



        if ($form->isSubmitted()) {

            $response = [];
            $redirect = $this->generateUrl($currentRoute, $urlParams);
            $isNext = $form->has('next') && $form->get('next')->isClicked();

            if ($form->isValid()) {

                $suiviDossierRepository = $em->getRepository(SuiviDossierWorkflow::class);
                $dossierWorkflow = $dossierWorkflowRepository->findOneBy(['dossier' => $dossier, 'workflow' => $current]);

                $suivi = $suiviDossierRepository->findOneBy(compact('dossierWorkflow'));

                if (!$suivi) {
                    $date = new \DateTime();
                    $suivi = new SuiviDossierWorkflow();
                    $suivi->setDossierWorkflow($dossierWorkflow);
                    $suivi->setDateDebut($date);
                    $suivi->setDateFin($date);
                }
                if ($isNext && $next) {

                    $url = [
                        'url' => $this->generateUrl($next['code'] . '_' . $next['route'], $urlParams),
                        'tab' => '#' . $next['route'],
                        'current' => '#' . $routeWithoutPrefix
                    ];
                    $hash = $next['route'];
                    $tabId = self::TAB_ID;
                    $redirect = $url['url'];

                    if (!$suivi->getEtat()) {
                        $dossier->setEtape($next['route']);
                        $suivi->setDateFin(new \DateTime());
                    }
                    $suivi->setEtat(true);
                } else {
                    $redirect = $this->generateUrl($currentRoute, $urlParams);
                }
                $modal = false;
                $em->persist($suivi);
                $em->persist($dossier);
                $em->flush();
                $data = null;

                $message       = 'Opération effectuée avec succès';
                $statut = 1;
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                    $this->addFlash('warning', $message);
                }
            }


            if ($isAjax) {
                return $this->json(compact('statut', 'message', 'redirect', 'data', 'url', 'tabId', 'modal'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }


        return $this->render("_admin/dossier/{$prefixe}/{$routeWithoutPrefix}.html.twig",  [
            'dossier' => $dossier,
            'route_without_prefix' => $routeWithoutPrefix,
            'form' => $form->createView()
        ]);
    }
}
