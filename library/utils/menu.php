<?php

/*
Copyright 2014 - Nicolas Devenet <nicolas@devenet.info>

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.

Code source hosted on https://github.com/nicolabricot/MoodPicker
*/

namespace Utils;

class Menu {

    private $items;
    private $isDropdown;
    
    const ICON_RIGHT = 1;
    const ICON_LEFT = 2;
    const NAVBAR = 'navbar';
    const NAVBAR_RIGHT = 'navbar_right';
    const DROPDOWN = true;

    public function __construct($isDropdown = false) {
        $this->items = array();
        $this->isDropdown = $isDropdown;
        return $this;
    }
    
    public function item($url, $text) {
        if ($this->isDropdown) { $this->items[] = new DropdownLink($url, $text);  }
        else { $this->items[] = new Link($url, $text); }
        return $this;
    }
    
    public function iconItem($url, $text, $icon, $placement = Menu::ICON_LEFT) {
        if ($this->isDropdown) { $this->items[] = new DropdownIconLink($url, $text, $icon, $placement);  }
        else { $this->items[] = new IconLink($url, $text, $icon, $placement); }
        return $this;
    }
    
    public function dropdown($menu, $text, $icon = NULL, $url = '#') {
        if (! $this->isDropdown) { $this->items[] = new Dropdown($menu, $text, $icon, $url); }
        return $this;
    }
    
    public function divider() {
        if ($this->isDropdown) { $this->items[] = new DropdownDivider(); }
        return $this;
    }
    
    public function header($text) {
        if($this->isDropdown) { $this->items[] = new DropdownHeader($text); }
        return $this;
    }
    

    public function generate($active = NULL) {
        $builder = '';
        $active = $this->activeItem($active);
        foreach ($this->items as $item) {
            $builder .= $item->build($active);
        }
        return $builder;
    }
    
    protected function activeItem($active) {
        if (is_null($active)) { return; }
        if (mb_substr($active, -5) == 'index') { return rtrim($active, 'index'); }
        return $active;
    }
    
    public function getItems() {
        return $this->items;
    }
    
}

abstract class MenuItem {
    protected $text;
    abstract public function build($active);
}

class Link extends MenuItem {
    protected $url;
    
    public function __construct($url, $text) {
        $this->text = $text;
        $this->url = $url;
    }
    
    public function build($active) {
        $builder = '<li'.($this->url == $active ? ' class="active"' : '').'>';
        $builder .= '<a href="'.$this->url.'">'.$this->text.'</a>';
        $builder .= '</li>';
        return $builder;
    }
}

class IconLink extends Link {
    protected $icon;
    protected $placement;
    
    public function __construct($url, $text, $icon, $placement) {
        parent::__construct($url, $text);
        $this->icon = $icon;
        $this->placement = $placement;
    }
    
    public function build($active) {
        $builder = '<li'.($this->url == $active ? ' class="active"' : '').'>';
        $builder .= '<a href="'.$this->url.'">';
        $builder .= $this->placement == Menu::ICON_LEFT ? '<span class="glyphicon glyphicon-'.$this->icon.'"></span> ' : '';
        $builder .= $this->text;
        $builder .= $this->placement == Menu::ICON_RIGHT ? ' <span class="glyphicon glyphicon-'.$this->icon.'"></span>' : '';
        $builder .= '</a>';
        $builder .= '</li>';
        return $builder;
    }
    
}

class Dropdown extends Link {
    protected $items;
    protected $icon;
    
    public function __construct($menu, $text, $icon = NULL, $url) {
        parent::__construct($url, $text);
        $this->items = $menu->getItems();
        $this->icon = $icon;    
    }
    
    public function build($active) {
        $builder = '<li class="dropdown">';
        $builder .= '<a href="'. $this->url .'" class="dropdown-toggle" data-toggle="dropdown">';
        $builder .= (! is_null($this->icon)) ? '<span class="glyphicon '. $this->icon .'"></span> ' : '';
        $builder .= $this->text .' <span class="caret"></span></a>';
        $builder .= '<ul class="dropdown-menu" role="menu">';
        foreach($this->items as $item) { $builder .= $item->build($active); }
        $builder .= '</ul></li>';
        return $builder;
    }
}

class DropdownLink extends Link {
    
    public function __construct($url, $text) {
        parent::__construct($url, $text);
    }
    
    public function build($active) {
        $builder = '<li><a href="'.$this->url.'">';
        $builder .= $this->url == $active ? '<strong>' : '';
        $builder .= $this->text;
        $builder .= $this->url == $active ? '</strong>' : '';
        $builder .= '</a></li>';
        return $builder;
    }
}

class DropdownIconLink extends IconLink {
    
    public function __construct($url, $text, $icon, $placement) {
        parent::__construct($url, $text, $icon, $placement);
    }
    
    public function build($active) {
        $builder = '<li><a href="'.$this->url.'">';
        $builder .= $this->url == $active ? '<strong>' : '';
        $builder .= $this->placement == Menu::ICON_LEFT ? '<span class="glyphicon glyphicon-'.$this->icon.'"></span> ' : '';
        $builder .= $this->text;
        $builder .= $this->placement == Menu::ICON_RIGHT ? ' <span class="glyphicon glyphicon-'.$this->icon.'"></span>' : '';
        $builder .= $this->url == $active ? '</strong>' : '';
        $builder .= '</a></li>';
        return $builder;
    }
}


class DropdownHeader extends MenuItem {
    public function __construct($text) {
        $this->$text = $text;
    }
    
    public function build($active = NULL) {
        return '<li class="dropdown-header" role="presentation">'.$this->text.'</li>';
    }
}

class DropdownDivider extends MenuItem {
    public function build($active = NULL) {
        return '<li role="presentation" class="divider"></li>';
    }
}