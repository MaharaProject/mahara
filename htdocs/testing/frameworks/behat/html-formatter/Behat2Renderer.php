<?php
/**
 * Behat2 renderer for Behat report
 * @author DaSayan <glennwall@free.fr>
 */

namespace emuse\BehatHTMLFormatter\Renderer ;

class Behat2Renderer
{

    public function __construct() {

    }


    /**
     * Renders before an exercice.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeExercise($obj) {

        $print = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Strict//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd'>
        <html xmlns ='http://www.w3.org/1999/xhtml'>
        <head>
            <meta http-equiv='Content-Type' content='text/html;charset=utf-8'/>
            <meta http-equiv='X-UA-Compatible' content='IE=edge'>
            <meta name='viewport' content='width=device-width, initial-scale=1'>

            <!-- Bootstrap -->
            <!-- Latest compiled and minified CSS -->
            <link rel='stylesheet' href='assets/Twig/css/bootstrap.min.css'>

            " . $this->getCSS() . "

            <!-- Optional theme -->
            <link rel='stylesheet' href='assets/Twig/css/style.css'>

            <script src='assets/Twig/js/Chart.min.js'></script>
            <title>Behat Test Suite</title>
        </head>
        <body>
        <div id='behat'>" ;

        return $print ;
    }

    /**
     * Renders after an exercice.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterExercise($obj) {
        //--> features results
        $strFeatPassed = '' ;
        $featTotal = 0;
        $sceTotal = 0;
        $stepsTotal = 0;

        if (null!==$obj->getPassedFeatures() && count($obj->getPassedFeatures()) > 0) {
            $strFeatPassed = ' <strong class="passed">'.count($obj->getPassedFeatures()).' success</strong>';
            $featTotal += count($obj->getPassedFeatures());
        }

        $strFeatFailed = '' ;
        $sumRes = 'passed' ;
        if (null!==$obj->getFailedFeatures() && count($obj->getFailedFeatures()) > 0) {
            $strFeatFailed = ' <strong class="failed">'.count($obj->getFailedFeatures()).' fail</strong>';
            $sumRes = 'failed' ;
            $featTotal += count($obj->getFailedFeatures());
        }

        //--> scenarios results
        $strScePassed = '' ;
        if (null!==$obj->getPassedScenarios() && count($obj->getPassedScenarios()) > 0) {
            $strScePassed = ' <strong class="passed">'.count($obj->getPassedScenarios()).' success</strong>';
            $sceTotal += count($obj->getPassedScenarios());
        }

        $strSceFailed = '' ;
        if (null!==$obj->getFailedScenarios() && count($obj->getFailedScenarios()) > 0) {
            $strSceFailed = ' <strong class="failed">'.count($obj->getFailedScenarios()).' fail</strong>';
            $sceTotal += count($obj->getFailedScenarios());
        }

        //--> steps results
        $strStepsPassed = '' ;
        if (null!==$obj->getPassedSteps() && count($obj->getPassedSteps()) > 0) {
            $strStepsPassed = ' <strong class="passed">'.count($obj->getPassedSteps()).' success</strong>';
            $stepsTotal += count($obj->getPassedSteps());
        }

        $strStepsPending = '' ;
        if (null!==$obj->getPendingSteps() && count($obj->getPendingSteps()) > 0) {
            $strStepsPending = ' <strong class="pending">'.count($obj->getPendingSteps()).' pending</strong>';
            $stepsTotal += count($obj->getPendingSteps());
        }

        $strStepsSkipped = '' ;
        if (null!==$obj->getSkippedSteps() && count($obj->getSkippedSteps()) > 0) {
            $strStepsSkipped = ' <strong class="skipped">'.count($obj->getSkippedSteps()).' skipped</strong>';
            $stepsTotal += count($obj->getSkippedSteps());
        }

        $strStepsFailed = '' ;
        if (null!==$obj->getFailedSteps() && count($obj->getFailedSteps()) > 0) {
            $strStepsFailed = ' <strong class="failed">'.count($obj->getFailedSteps()).' fail</strong>';
            $stepsTotal += count($obj->getFailedSteps());
        }


        //list of pending steps to display
        $strPendingList = '' ;
        if (null!==$obj->getPendingSteps() && count($obj->getPendingSteps()) > 0) {
            foreach($obj->getPendingSteps() as $pendingStep) {
                $strPendingList .= '
                    <li>' . $pendingStep->getKeyword() . ' ' . $pendingStep->getText() . '</li>' ;
            }
                $strPendingList = '
            <div class="pending">Pending steps :
                <ul>' . $strPendingList . '
                </ul>
            </div>';
        }


        $print = '
        <div class="summary '.$sumRes.'">
            <div class="counters">
                <p class="features">
                    '.$featTotal.' features ('.$strFeatPassed.$strFeatFailed.' )
                </p>
                <p class="scenarios">
                    '.$sceTotal.' scenarios ('.$strScePassed.$strSceFailed.' )
                </p>
                <p class="steps">
                    '.$stepsTotal.' steps ('.$strStepsPassed.$strStepsPending.$strStepsSkipped.$strStepsFailed.' )
                </p>
                <p class="time">
                '.$obj->getTimer().' - '.$obj->getMemory().'
                </p>
            </div>
            <div class="switchers">
                <a href="javascript:void(0)" id="behat_show_all">
                    <span class="icon icon-chevron-down"></span>
                    <span class="sr-only">Show all tests</span>
                </a>
                <a href="javascript:void(0)" id="behat_hide_all">
                    <span class="icon icon-chevron-up"></span>
                    <span class="sr-only">Hide all tests</span>
                </a>
            </div>
        </div> ' .$strPendingList. '
    </div>' . $this->getJS() . '
</body>
</html>' ;

        return $print ;

    }


    /**
     * Renders before a suite.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeSuite($obj) {
        $print = '
        <div class="suite">Suite : ' . $obj->getCurrentSuite()->getName() . '</div>';

        return $print ;

    }

    /**
     * Renders after a suite.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterSuite($obj) {
        return '' ;
    }

    /**
     * Renders before a feature.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeFeature($obj) {

        //feature head
        $print = '
        <div class="feature">
            <h2>
                <span id="feat'.$obj->getCurrentFeature()->getId().'" class="keyword"> Feature: </span>
                <span class="title">' . $obj->getCurrentFeature()->getName() . '</span>
            </h2>
            <p>' . $obj->getCurrentFeature()->getDescription() . '</p>
            <ul class="tags">' ;
        foreach($obj->getCurrentFeature()->getTags() as $tag) {
            $print .= '
                <li>@' . $tag .'</li>' ;
        }
        $print .= '
            </ul>' ;

        //TODO path is missing (?)

        return $print ;
    }

    /**
     * Renders after a feature.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterFeature($obj) {
        //list of results
        $print = '
            <div class="featureResult '.$obj->getCurrentFeature()->getPassedClass().'">Feature has ' . $obj->getCurrentFeature()->getPassedClass() ;

        //percent only if failed scenarios
        if ($obj->getCurrentFeature()->getTotalAmountOfScenarios() > 0 && $obj->getCurrentFeature()->getPassedClass() === 'failed') {
            $print .= '
                <span>Scenarios passed : ' . round($obj->getCurrentFeature()->getPercentPassed(), 2) . '%,
                Scenarios failed : ' . round($obj->getCurrentFeature()->getPercentFailed(), 2) . '%</span>' ;
        }

        $print .= '
            </div>
        </div>';


        return $print ;
    }

    /**
     * Renders before a scenario.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeScenario($obj) {
        //scenario head
        $print = '
            <div class="scenario">
                <ul class="tags">' ;
        foreach($obj->getCurrentScenario()->getTags() as $tag) {
            $print .= '
                    <li>@' . $tag .'</li>';
        }
        $print .= '
                </ul>';

        $print .= '
                <h3>
                    <span class="keyword">' . $obj->getCurrentScenario()->getId() . ' Scenario: </span>
                    <span class="title">' . $obj->getCurrentScenario()->getName() . '</span>
                </h3>
                <ol>' ;

        //TODO path is missing

        return $print ;
    }

    /**
     * Renders after a scenario.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterScenario($obj) {
        $print = '
                </ol>
            </div>';

        return $print ;
    }

    /**
     * Renders before an outline.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeOutline($obj) {
        return '' ;
    }

    /**
     * Renders after an outline.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterOutline($obj) {
        return '' ;
    }

    /**
     * Renders before a step.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderBeforeStep($obj) {

        return '' ;
    }

    /**
     * Renders after a step.
     *
     * @param object   : BehatHTMLFormatter object
     * @return string  : HTML generated
     */
    public function renderAfterStep($obj) {

        $steps = $obj->getCurrentScenario()->getSteps() ;
        $step = end($steps) ; //needed because of strict standards

        //path displayed only if available (it's not available in undefined steps)
        $strPath = '' ;
        if ($step->getDefinition() !== NULL ) {
            $strPath = $step->getDefinition()->getPath() ;
        }

        $stepResultClass = '' ;
        if ($step->isPassed()) {
            $stepResultClass = 'passed' ;
        }
        if ($step->isFailed()) {
            $stepResultClass = 'failed' ;
        }
        if ($step->isSkipped()) {
            $stepResultClass = 'skipped' ;
        }
        if ($step->isPending()) {
            $stepResultClass = 'pending' ;
        }

        $print = '
                    <li class="'.$stepResultClass.'">
                        <div class="step">
                            <span class="keyword">' . $step->getKeyWord() . ' </span>
                            <span class="text">' . $step->getText() . ' </span>
                            <span class="path">' . $strPath . '</span>
                        </div>' ;
        if (!empty($step->getException())) {
            $print .= '
                        <pre class="backtrace">' . $step->getException() . '</pre>' ;
        }
        $print .=  '
                    </li>';

        return $print ;
    }




    /**
     * To include CSS
     *
     * @return string  : HTML generated
     */
    public function getCSS() {

        return "<style type='text/css'>

            </style>

            <style type='text/css' media='print'>
                body {
                    padding:0px;
                }

                #behat {
                    font-size:11px;
                }

                #behat .jq-toggle > .scenario,
                #behat .jq-toggle > .scenario .examples,
                #behat .jq-toggle > ol {
                    display:block;
                }

                #behat .summary {
                    position:relative;
                }

                #behat .summary .counters {
                    border:none;
                }

                #behat .summary .switchers {
                    display:none;
                }

                #behat .step .path {
                    display:none;
                }

                #behat .jq-toggle > h2:after,
                #behat .jq-toggle > h3:after {
                    content:'';
                    font-weight:bold;
                }

                #behat .jq-toggle-opened > h2:after,
                #behat .jq-toggle-opened > h3:after {
                    content:'';
                    font-weight:bold;
                }

                #behat .scenario > ol li,
                #behat .scenario .examples > ol li {
                    border-left:none;
                }
            </style>" ;

    }

    /**
     * To include JS
     *
     * @return string  : HTML generated
     */
    public function getJS() {

        return "<script type='text/javascript' src='assets/Twig/js/jquery.js'></script>
        <script type='text/javascript'>
            $(function() {
                Array.prototype.diff = function(a) {
                    return this.filter(function(i) {return a.indexOf(i) < 0;});
                };

                $('#behat .feature h2').on('click', function(){
                    $(this).parent().toggleClass('jq-toggle-opened');
                }).parent().addClass('jq-toggle');

                $('#behat .scenario h3').on('click', function(){
                    $(this).parent().toggleClass('jq-toggle-opened');
                }).parent().addClass('jq-toggle');

                $('#behat_show_all').on('click', function(){
                    $('#behat .feature').addClass('jq-toggle-opened');
                    $('#behat .scenario').addClass('jq-toggle-opened');
                });

                $('#behat_hide_all').on('click', function(){
                    $('#behat .feature').removeClass('jq-toggle-opened');
                    $('#behat .scenario').removeClass('jq-toggle-opened');
                });

                $('#behat .summary .counters .scenarios .passed')
                    .addClass('switcher')
                    .on('click', function(){
                        var scenario = $('.feature .scenario:not(:has(.failed, .pending))');
                        var feature  = scenario.parent();

                        $('#behat_hide_all').trigger('click');

                        scenario.addClass('jq-toggle-opened');
                        feature.addClass('jq-toggle-opened');
                    });


                $('#behat .summary .counters .scenarios .failed')
                    .addClass('switcher')
                    .on('click', function(){
                        var scenario = $('.feature .scenario:has(.failed, .pending)');
                        var feature = scenario.parent();

                        $('#behat_hide_all').trigger('click');

                        scenario.addClass('jq-toggle-opened');
                        feature.addClass('jq-toggle-opened');
                    });

                $('#behat .summary .counters .steps .passed')
                    .addClass('switcher')
                    .on('click', function(){
                        var scenario = $('.feature .scenario:has(.passed)');
                        var feature  = scenario.parent();

                        $('#behat_hide_all').trigger('click');

                        scenario.addClass('jq-toggle-opened');
                        feature.addClass('jq-toggle-opened');
                    });

                $('#behat .summary .counters .steps .failed')
                    .addClass('switcher')
                    .on('click', function(){
                        var scenario = $('.feature .scenario:has(.failed)');
                        var feature = scenario.parent();

                        $('#behat_hide_all').trigger('click');

                        scenario.addClass('jq-toggle-opened');
                        feature.addClass('jq-toggle-opened');
                    });

                $('#behat .summary .counters .steps .skipped')
                    .addClass('switcher')
                    .on('click', function(){
                        var scenario = $('.feature .scenario:has(.skipped)');
                        var feature = scenario.parent();

                        $('#behat_hide_all').trigger('click');

                        scenario.addClass('jq-toggle-opened');
                        feature.addClass('jq-toggle-opened');
                    });

                $('#behat .summary .counters .steps .pending')
                    .addClass('switcher')
                    .on('click', function(){
                        var scenario = $('.feature .scenario:has(.pending)');
                        var feature = scenario.parent();

                        $('#behat_hide_all').trigger('click');

                        scenario.addClass('jq-toggle-opened');
                        feature.addClass('jq-toggle-opened');
                    });
            });
        </script>" ;

    }
}
