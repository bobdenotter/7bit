<?php

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * "root"
 */
$app->match("/", function(Silex\Application $app, Request $request) {

    $twigvars = array();


    if ($request->getMethod() == "POST") {
        // dump($request->request->all());

        if ($request->get('convert') == "to") {
            $sevenbit = new SevenBit();
            $twigvars['in'] = trim($request->get('in'));
            $twigvars['out'] = $sevenbit->encode($request->get('in'));
            $twigvars['check'] = $sevenbit->decode($twigvars['out']);
        }

        if ($request->get('convert') == "from") {
            $sevenbit = new SevenBit();
            $twigvars['out'] = trim($request->get('out'));
            $twigvars['in'] = $sevenbit->decode($request->get('out'));
            $twigvars['check'] = $sevenbit->encode($twigvars['in']);
        }

    }


    // $in = "Pompidom - wat een jolijt! - trala Hopsala, flopperikidee. !! :-) 12345678901234567890!@#$%^&*()";

    // $sevenbit = new SevenBit();
    // $out = $sevenbit->encode($in);
    // $sevenbit->decode($out);


    // $twigvars['in'] = $in;
    // $twigvars['out'] = $out;

    return $app['twig']->render('index.twig', $twigvars);


});

