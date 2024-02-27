<?php

namespace App\Controller;

use App\Entity\Type;
use App\Form\TypeActeType;
use App\Service\FormError;
use App\Service\ActionRender;
use App\Repository\TypeRepository;
use App\Repository\DossierRepository;
use App\Repository\WorkflowRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Omines\Adapter\ArrayAdapter;
use Omines\DataTablesBundle\DataTableFactory;
use Symfony\Component\HttpFoundation\Request;
use Omines\DataTablesBundle\Column\TextColumn;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Omines\DataTablesBundle\Column\NumberColumn;
use Omines\DataTablesBundle\Column\DateTimeColumn;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

/**
 * @Route("/admin")
 * il s'agit du typeActe des module
 */
class TypeActeController extends AbstractController
{

    /**
     * @Route("/typeActe/{id}/confirmation", name="typeActe_confirmation", methods={"GET"})
     * @param $id
     * @param Type $parent
     * @return Response
     */
    public function confirmation($id,Type $parent): Response
    {
        return $this->render('_admin/modal/confirmation.html.twig',[
            'id'=>$id,
            'action'=>'typeActe',
        ]);
    }

    /**
     * @Route("/typeActe", name="typeActe")
     * @param TypeRepository $repository
     * @return Response
     */
    public function index(Request $request,
                          DataTableFactory $dataTableFactory,
                          TypeRepository $typeacterepository): Response
    {

        $table = $dataTableFactory->create();

        $user = $this->getUser();

        $requestData = $request->request->all();

        $offset = intval($requestData['start'] ?? 0);
        $limit = intval($requestData['length'] ?? 10);

        $searchValue = $requestData['search']['value'] ?? null;



        $totalData = $typeacterepository->countAll();
        $totalFilteredData = $typeacterepository->countAll($searchValue);
        $data = $typeacterepository->getAll($limit, $offset,  $searchValue);

//dd($data);


        $table->createAdapter(ArrayAdapter::class, [
            'data' => $data,
            'totalRows' => $totalData,
            'totalFilteredRows' => $totalFilteredData
        ]) ->setName('dt_');
        ;


        $table
            ->add('code', TextColumn::class, ['label' => 'Code', 'className' => 'w-20px'])
            ->add('titre', TextColumn::class, ['label' => 'Titre', 'className' => 'w-100px']);




        $renders = [
            'edit' =>  new ActionRender(function () {
                return true;
            }),
            'delete' => new ActionRender(function (){
                return true;
            }),
            'details' => new ActionRender(function () {
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
                'label' => 'Actions'
                , 'field' => 'id'
                , 'orderable' => false
                ,'globalSearchable' => false
                ,'className' => 'grid_row_actions'
                , 'render' => function ($value, $context) use ($renders) {

                    $options = [
                        'default_class' => 'btn btn-xs btn-clean btn-icon mr-2 ',
                        'target' => '#extralargemodal1',

                        'actions' => [
                            'edit' => [
                                'url' => $this->generateUrl('typeActe_edit', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-edit'
                                , 'attrs' => ['class' => 'btn-success']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['edit'];
                                })
                            ],
                            'details' => [
                                'url' => $this->generateUrl('typeActe_show', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-eye'
                                , 'attrs' => ['class' => 'btn-primary']
                                , 'render' => new ActionRender(function () use ($renders) {
                                    return $renders['details'];
                                })
                            ],
                            'delete' => [
                                'url' => $this->generateUrl('typeActe_delete', ['id' => $value])
                                , 'ajax' => true
                                , 'icon' => '%icon% fe fe-trash-2'
                                , 'target' => '#smallmodal'
                                , 'attrs' => ['class' => 'btn-danger', 'title' => 'Suppression']

                                ,  'render' => new ActionRender(function () use ($renders) {
                                    return $renders['delete'];
                                })
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

        return $this->render('_admin/typeActe/index.html.twig', ['datatable' => $table, 'titre' => 'Liste des types acte']);
    }


    /**
     * @Route("/typeActe/new", name="typeActe_new", methods={"GET","POST"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function new(Request $request, FormError $formError, EntityManagerInterface  $em): Response
    {
        $typeActe = new Type();
        $form = $this->createForm(TypeActeType::class,$typeActe, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeActe_new')
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('typeActe');

            if ($form->isValid()) {
                $typeActe->setActive(1);
                $em->persist($typeActe);
                $em->flush();

                $data = true;
                $message       = 'Opération effectuée avec succès';
                $this->addFlash('success', $message);
            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }
            }


            /*  }*/
            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/typeActe/new.html.twig', [
            'typeActe' => $typeActe,
            'form' => $form->createView(),
            'titre' => 'Type Acte',
        ]);
    }

    /**
     * @Route("/typeActe/{id}/edit", name="typeActe_edit", methods={"GET","POST"})
     * @param Request $request
     * @param TypeActe $typeActe
     * @param EntityManagerInterface $em
     * @return Response
     */
    public function edit(Request $request, FormError $formError, Type $typeActe, EntityManagerInterface  $em): Response
    {

        $form = $this->createForm(TypeActeType::class,$typeActe, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeActe_edit',[
                'id'=>$typeActe->getId(),
            ])
        ]);
        $form->handleRequest($request);
        $data = null;
        $isAjax = $request->isXmlHttpRequest();

        if($form->isSubmitted())
        {
            $statut = 1;
            $response = [];
            $redirect = $this->generateUrl('typeActe');

            if($form->isValid()){
                $em->persist($typeActe);
                $em->flush();

                $message       = 'Opération effectuée avec succès';
                $data = true;
                $this->addFlash('success', $message);

            } else {
                $message = $formError->all($form);
                $statut = 0;
                if (!$isAjax) {
                  $this->addFlash('warning', $message);
                }
            }

            if ($isAjax) {
                return $this->json( compact('statut', 'message', 'redirect', 'data'));
            } else {
                if ($statut == 1) {
                    return $this->redirect($redirect);
                }
            }
        }

        return $this->render('_admin/typeActe/edit.html.twig', [
            'typeActe' => $typeActe,
            'form' => $form->createView(),
            'titre' => 'Type Acte',
        ]);
    }

    /**
     * @Route("/typeActe/{id}/show", name="typeActe_show", methods={"GET"})
     * @param Type $typeActe
     * @return Response
     */
    public function show(Type $typeActe): Response
    {
        $form = $this->createForm(TypeActeType::class,$typeActe, [
            'method' => 'POST',
            'action' => $this->generateUrl('typeActe_show',[
                'id'=>$typeActe->getId(),
            ])
        ]);

        return $this->render('_admin/typeActe/voir.html.twig', [
            'typeActe' => $typeActe,
            'titre' => 'Type Acte',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/typeActe/{id}/active", name="typeActe_active", methods={"GET"})
     * @param $id
     * @param Type $typeActe
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function active($id,Type $typeActe, EntityManagerInterface $entityManager): Response
    {



        if ($typeActe->getActive() == 1){

            $typeActe->setActive(0);

        }else{

            $typeActe->setActive(1);

        }

        $entityManager->persist($typeActe);
        $entityManager->flush();
        return $this->json([
            'code'=>200,
            'message'=>'ça marche bien',
            'active'=>$typeActe->getActive(),
        ],200);


    }


    /**
     * @Route("/typeActe/{id}/delete", name="typeActe_delete", methods={"POST","GET","DELETE"})
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param Type $typeActe
     * @return Response
     */
    public function delete(Request $request, EntityManagerInterface $em,Type $typeActe): Response
    {


        $form = $this->createFormBuilder()
            ->setAction(
                $this->generateUrl(
                    'typeActe_delete'
                    ,   [
                        'id' => $typeActe->getId()
                    ]
                )
            )
            ->setMethod('DELETE')
            ->getForm();

        
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {

            $em->remove($typeActe);
            $em->flush();

            $redirect = $this->generateUrl('typeActe');

            $message = 'Opération effectuée avec succès';

            $response = [
                'statut'   => 1,
                'message'  => $message,
                'data' => true,
                'redirect' => $redirect,
            ];

            $this->addFlash('success', $message);

            if (!$request->isXmlHttpRequest()) {
                return $this->redirect($redirect);
            } else {
                return $this->json($response);
            }



        }
        return $this->render('_admin/typeActe/delete.html.twig', [
            'typeActe' => $typeActe,
            'form' => $form->createView(),
        ]);
    }

}
