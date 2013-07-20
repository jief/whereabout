<?php

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app['debug'] = true;

$app->register(new \Devture\SilexProvider\DoctrineMongoDB\ServicesProvider('mongodb', array()));

$app['db'] = $app->share(function ($app) {
  return $app['mongodb.connection']->selectDatabase('whereabout');
});

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  'twig.path' => __DIR__.'/../src/views',
  'twig.options' => array('cache' => __DIR__.'/../src/cache')
));

$app->get('/', function (Silex\Application $app)  {
  $offset = $app['request']->query->get('o', 0);
  $currentWeek = date('W', strtotime($offset . " week")); 
  $key = date('Y-W', strtotime($offset . " week"));
  $dayOfWeek = date('N');
  $monday = ($dayOfWeek == 1) ? strtotime( $offset . " week") : strtotime( $offset . " week last Monday");  
  
  $data = array(
    "currentWeek" => $currentWeek,
    "offset" => $offset,
    "monday" => $monday,   
    "values" => $app['db']->where->find(array('week' => $key)),
  );

  return $app['twig']->render('index.html.twig',  $data);
});

$app->get('/add', function (Silex\Application $app)  {
  $offset = $app['request']->query->get('o', 0);
  $currentWeek = date('W', strtotime( $offset . " week")); 
  $data = array(
    'offset' => $offset,
    'currentWeek' => $currentWeek,
  );
  return $app['twig']->render('form.html.twig', $data);
});

$app->post('/save', function (Silex\Application $app) {
  $values = $app['request']->request->all();  

  $key = date('Y-W', strtotime($values['o'] . " week"));
  $data = array(
    'week' => $key, 
    'who' => $values['who'],
    'days' => array(
      'monday' => $values['monday'],
      'tuesday' => $values['tuesday'],
      'wednesday' => $values['wednesday'],
      'thursday' => $values['thursday'],
      'friday' => $values['friday'],
    )
  );

  $collection = $app['db']->where;
  if(isset($values['_id']) && $values['_id'] != '') { 
    $data['_updated'] = time();
    $collection->update(array('_id' => new MongoId($values['_id'])), array('$set' => $data)); 
  } else {
    $data['_created'] = time();
    $collection->insert($data);
  }
  return $app->redirect('./?o=' . $values['o']);
});

$app->get('edit/{id}', function(Silex\Application $app, $id) {
  $offset = $app['request']->query->get('o', 0);
  $currentWeek = date('W', strtotime( $offset . " week")); 

  $collection = $app['db']->where;
  $value = $collection->findOne(array('_id' => new MongoId($id)));

  $data = array(
    'offset' => $offset,
    'currentWeek' => $currentWeek,
    'value' => $value,
  );

  return $app['twig']->render('form.html.twig', $data);

});

$app->get('/delete/{id}', function(Silex\Application $app, $id) {
  $offset = $app['request']->query->get('o', 0);
  $collection = $app['db']->where;
  $collection->remove(array('_id' => new MongoId($id)));
  return $app->redirect('../?o=' . $offset);
});

return $app;