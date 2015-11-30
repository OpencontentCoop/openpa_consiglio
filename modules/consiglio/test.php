<?php

echo '<pre>';

$push = OpenPAConsiglioPushNotifier::instance();
$push->emit('ping',array('test'=> 'tost'));


//$seduta = OCEditorialStuffHandler::instance( 'seduta' )->getFactory()->instancePost( array( 'object_id' => $_GET['s'] ) );
//$helper = new OpenPAConsiglioPresenzaHelper( $seduta );
//$data = $helper->run();
//$values = $helper->getPercent();
//echo '<pre>';
//print_r($values);
//print_r($data);
eZDisplayDebug();
eZExecution::cleanExit();

//$factory = OCEditorialStuffHandler::instance( 'votazione' )->getFactory();
//$votazione = new Votazione( array( 'object_id' => 2194 ), $factory );
//$votazione->stop();
//echo '<pre>';
//print_r( $votazione->jsonSerialize() );

//$dateTime = new DateTime();
//        $now = $dateTime->format( 'Y-m-d H:i:s' );
//
//$url = 'http://consiglio.u-hopper.com/api/consiglio/seduta';
//$values = array(
//    'id' => 1,
//    'type' => 'in_progress',
//    'date' => $now
//);
//$ch = curl_init();
//curl_setopt( $ch, CURLOPT_URL, $url );
//curl_setopt( $ch, CURLOPT_POST, 1 );
//curl_setopt( $ch, CURLOPT_POSTFIELDS, $values );
//$result = curl_exec( $ch );
//var_dump( $result );
/*
$data = array(
    'object_id'          => 1,
    'user_id'            => 14,
    'type'               => 'email',
    'subject'            => 'Subject',
    'body'               => 'Body'
);


$item = OpenPAConsiglioNotificationItem::create($data);

echo '<pre>';
print_r($item);

echo $item->__get('subject');

//$item->send();


$items = OpenPAConsiglioNotificationItem::fetchItemsToSend(
    OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT
);

OpenPAConsiglioNotificationItem::sendByType(
    OpenPAConsiglioNotificationTransport::DIGEST_TRANSPORT
);

*/

//$pdf = new Pdf();
//$pdf->addPage( $url );
//$pdf->send( $node->attribute('name') . '.pdf' );
//eZExecution::cleanExit();
//
//use mikehaertl\wkhtmlto\Pdf;
//
//// Initialize the PDF using this library: https://github.com/mikehaertl/phpwkhtmltopdf
//$pdf = new Pdf();
//
//// specify wkhtmltopdf options; see: http://wkhtmltopdf.org/usage/wkhtmltopdf.txt
//$options = array(
//    'page-width' => '216mm',
//    'page-height' => '297mm',
//    'dpi' => 300,
//    'image-quality' => 100,
//    'margin-top' => '40mm',
//    'margin-right' => '14mm',
//    'margin-bottom' => '0',
//    'margin-left' => '14mm',
//    'header-spacing' => 15,
//    //'footer-spacing' => 5,
//    'disable-smart-shrinking',
//    'no-outline',
//    'user-style-sheet' => 'extension/openpa_consiglio/design/standard/stylesheets/pdf.css',
//    'footer-html' => 'extension/openpa_consiglio/design/standard/templates/pdf/footer.html',
//    'header-html' => 'extension/openpa_consiglio/design/standard/templates/pdf/header.html'
//);
//
//$pdf->setOptions( $options );
//
//// uses eZ Template to build the cover and frontpage
//$tpl = eZTemplate::factory();
//$tpl->setVariable( 'object', $object );
//$pdf->addPage( $tpl->fetch( 'design:pdf/cover.tpl' ) );
//$pdf->addPage( $tpl->fetch( 'design:pdf/frontpage.tpl' ) );

// Adds a Table of Contents
//$pdf->addToc(array('user-style-sheet' => 'extension/myextension/design/standard/stylesheets/pdf.css','xsl-style-sheet' => 'extension/myextension/design/standard/stylesheets/toc.xsl'));

// Fill the body of the PDF

//$content = '<html><head></head><body id="pdf-content"><p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Integer tellus purus, accumsan in malesuada nec, euismod ut tellus. Donec felis ante, posuere et porta vel, dignissim sollicitudin purus. Pellentesque congue varius accumsan. Duis justo massa, dictum eu justo at, pellentesque fringilla ligula. Vestibulum vel erat nibh. Integer feugiat egestas enim, ornare lacinia arcu vulputate at. Curabitur turpis velit, luctus iaculis pharetra suscipit, tempor et dolor. Vivamus et risus a nunc scelerisque vestibulum quis eu purus. Aliquam erat volutpat. Nunc neque eros, blandit quis arcu ultricies, pellentesque malesuada turpis. Duis a leo tristique, vehicula sapien non, tristique neque. Nullam lacus mauris, condimentum quis massa ac, dictum malesuada nisl.</p></body></html>';

//$pdf->addPage( $content );
//
//$pdf->addPage( $tpl->fetch( 'design:pdf/presenza/presenza.tpl' ));
//
//
//// Adds the backpage
////$pdf->addPage( $tpl->fetch( 'design:pdf/backpage.tpl' ) );
//
//// Downloads the PDF
//$pdf->send( );

eZExecution::cleanExit();

