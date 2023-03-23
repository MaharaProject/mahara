  <?php

  $finder = Symfony\Component\Finder\Finder::create()
    ->in([
      'htdocs',
    ]);

  // Custom rule for Mahara to have uncuddled control structures
  return (new PhpCsFixer\Config())
    ->setRules([
      'control_structure_continuation_position' => ['position' => 'next_line'],
    ])->setFinder($finder);
