<?php
namespace SimpleThings;

class TeamsMessage {
    private $color;
    private $summary, $title, $text;
    private $potentialAction;
    private $sections;

    public function __construct($text = null, $title = null)
    {
        if ($text !== null) {
            $this->setTitle($text);
        }
        if ($title !== null) {
            $this->SetTitle($title);
        }
    }

    public function getJson()
    {
        $msg = array();
        if (isset($this->summary)) {
            $msg['summary'] = $this->summary;
        }
        if (isset($this->title)) {
            $msg['title'] = $this->title;
        }
        if (isset($this->text)) {
            $msg['text'] = $this->text;
        }
        if (isset($this->color)) {
            $msg['themeColor'] = $this->color;
        }
        
        if (isset($this->sections)) {
            $msg['sections'] = $this->sections;
        }
        if (isset($this->potentialAction)) {
            $msg['potentialAction'] = $this->potentialAction;
        }
        
        return json_encode($msg);
    }
    
    public function setText($text)
    {
        $this->text = $text;
    }
    
    public function setTitle($title)
    {
        $this->title = $title;
    }
   
    
    public function setColor($color)
    {
        $this->color = $color;
    }
    
    public function addActivity($text, $title = null, $image = null)
    {
        $activity['activityTitle'] = $title;
        if ($text !== null) {
            $activity['activityText'] = $text; 
        }
        if ($image !== null) {
            $activity['activityImage'] = $image;
        }
        
        $this->sections[] = $activity;
    }

    public function addFacts($title, array $array)
    {
        $section['title'] = $title;
        foreach ($array as $name => $value) {
            $section['facts'][] = [
            'name' => $name,
            'value' => $value
            ];
        }

        $this->sections[] = $section;
    }
    
    public function addImage($title, $image)
    {
        $this->addImages($title, array($image));
    }
    
    public function addImages($title, array $images)
    {
        $section['title'] = $title;
        foreach ($images as $image) {
            $section['images'][] = ['image' => $image];
        }
        $this->sections[] = $section;
    }

    public function addCommentAction($text, $placeholder, $button, $target, $id, $body)
    {
        $this->addPotentialAction(
            [
                'text' => $text,
                'inputs' => [
                    $this->makeInput(['id' => $id, 'title' => $placeholder], 'TextInput')
                ],
                'actions' => [
                    $this->makeAction(['text' => $button, 'target' => $target, 'body' => $body], 'HttpPost')
                ]
            ],
            'Actioncard');
    }

    public function addPotentialAction($options, $type = null)
    {
        $this->potentialAction[] = $this->makeAction($options, $type);
    }

    public function makeAction($options, $type = null)
    {
        $type = $type ? $type : 'ViewAction';

        switch($type) {
            case 'ViewAction':
                $action = [
                    'name' => $options['text'],
                    'target' => $options['target']
                ];
                break;
            case 'OpenUri':
                $action = [
                    'name' => $options['text'],
                    'targets' => $options['targets']
                ];
                break;
            case 'HttpPost':
                $action = [
                    'name' => $options['text'],
                    'target' => $options['target']
                ];
                if (isset($options['headers'])) {
                    $action['headers'] = $options['headers'];
                }
                if (isset($options['body'])) {
                    $action['body'] = $options['body'];
                }
                if (isset($options['bodyContentType'])) {
                    $action['bodyContentType'] = $options['bodyContentType'];
                }
                break;
            case 'ActionCard':
                $action = [
                    'name' => $options['text'],
                    'inputs' => $options['inputs'],
                    'actions' => $options['actions']
                ];
                break;
            default:
                return [];
        }

        $action['@type'] = $type;

        return $action;
    }

    public function makeInput($options, $type = null)
    {
        $type = $type ? $type : 'TextInput';

        $input = [
            '@type' => $type,
            'title' => $options['text'],
            'id' => $options['id'],
        ];

        if (isset($options['isRequired'])) {
            $input['isRequired'] = $options['isRequired'];
        }
        if (isset($options['value'])) {
            $input['value'] = $options['value'];
        }

        switch($type) {
            case 'TextInput':
                if (isset($options['isMultiline'])) {
                    $input['isMultiline'] = $options['isMultiline'];
                }
                if (isset($options['maxLength'])) {
                    $input['maxLength'] = $options['maxLength'];
                }
                break;
            case 'DateInput':
                if (isset($options['includeTime'])) {
                    $input['includeTime'] = $options['includeTime'];
                }
                break;
            case 'MultichoiceInput':
                $input['choices'] = $options['choices'];
                if (isset($options['isMultiSelect'])) {
                    $input['isMultiSelect'] = $options['isMultiSelect'];
                }
                if (isset($options['style'])) {
                    $input['style'] = $options['style'];
                }
                break;
            default:
                return [];
        }

        return $input;
    }

    public function send($url) {
        $json = $this->getJson();
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($json))
        );

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        if ($result === false || $info['http_code'] != 200) {
            print_r(curl_getinfo($ch));
        }
    }
}