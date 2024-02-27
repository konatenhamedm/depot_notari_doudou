/*!
 * jquery-confirm v3.3.2 (http://craftpip.github.io/jquery-confirm/)
 * Author: boniface pereira
 * Website: www.craftpip.com
 * Contact: hey@craftpip.com
 *
 * Copyright 2013-2017 jquery-confirm
 * Licensed under MIT (https://github.com/craftpip/jquery-confirm/blob/master/LICENSE)
 */
@-webkit-keyframes jconfirm-spin {
    from {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }
    to {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
    }
}
@keyframes jconfirm-spin {
    from {
        -webkit-transform: rotate(0deg);
        transform: rotate(0deg);
    }
    to {
        -webkit-transform: rotate(360deg);
        transform: rotate(360deg);
    }
}
body[class*=jconfirm-no-scroll-] {
    overflow: hidden !important;
}
.jconfirm {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    z-index: 99999999;
    font-family: inherit;
    overflow: hidden;
}
.jconfirm .jconfirm-bg {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    -webkit-transition: opacity .4s;
    transition: opacity .4s;
}
.jconfirm .jconfirm-bg.jconfirm-bg-h {
    opacity: 0 !important;
}
.jconfirm .jconfirm-scrollpane {
    -webkit-perspective: 500px;
    perspective: 500px;
    -webkit-perspective-origin: center;
    perspective-origin: center;
    display: table;
    width: 100%;
    height: 100%;
}
.jconfirm .jconfirm-row {
    display: table-row;
    width: 100%;
}
.jconfirm .jconfirm-cell {
    display: table-cell;
    vertical-align: middle;
}
.jconfirm .jconfirm-holder {
    max-height: 100%;
    padding: 50px 0;
}
.jconfirm .jconfirm-box-container {
    -webkit-transition: -webkit-transform;
    transition: -webkit-transform;
    transition: transform;
    transition: transform, -webkit-transform;
}
.jconfirm .jconfirm-box-container.jconfirm-no-transition {
    -webkit-transition: none !important;
    transition: none !important;
}
.jconfirm .jconfirm-box {
    background: white;
    border-radius: 4px;
    position: relative;
    outline: none;
    padding: 15px 15px 0;
    overflow: hidden;
    margin-left: auto;
    margin-right: auto;
}
@-webkit-keyframes type-blue {
    1%,
    100% {
        border-color: #3498db;
}
    50% {
        border-color: #5faee3;
}
}
@keyframes type-blue {
    1%,
    100% {
        border-color: #3498db;
}
    50% {
        border-color: #5faee3;
}
}
@-webkit-keyframes type-green {
    1%,
    100% {
        border-color: #2ecc71;
}
    50% {
        border-color: #54d98c;
}
}
@keyframes type-green {
    1%,
    100% {
        border-color: #2ecc71;
}
    50% {
        border-color: #54d98c;
}
}
@-webkit-keyframes type-red {
    1%,
    100% {
        border-color: #e74c3c;
}
    50% {
        border-color: #ed7669;
}
}
@keyframes type-red {
    1%,
    100% {
        border-color: #e74c3c;
}
    50% {
        border-color: #ed7669;
}
}
@-webkit-keyframes type-orange {
    1%,
    100% {
        border-color: #f1c40f;
}
    50% {
        border-color: #f4d03f;
}
}
@keyframes type-orange {
    1%,
    100% {
        border-color: #f1c40f;
}
    50% {
        border-color: #f4d03f;
}
}
@-webkit-keyframes type-purple {
    1%,
    100% {
        border-color: #9b59b6;
}
    50% {
        border-color: #b07cc6;
}
}
@keyframes type-purple {
    1%,
    100% {
        border-color: #9b59b6;
}
    50% {
        border-color: #b07cc6;
}
}
@-webkit-keyframes type-dark {
    1%,
    100% {
        border-color: #34495e;
}
    50% {
        border-color: #46627f;
}
}
@keyframes type-dark {
    1%,
    100% {
        border-color: #34495e;
}
    50% {
        border-color: #46627f;
}
}
.jconfirm .jconfirm-box.jconfirm-type-animated {
    -webkit-animation-duration: 2s;
    animation-duration: 2s;
    -webkit-animation-iteration-count: infinite;
    animation-iteration-count: infinite;
}
.jconfirm .jconfirm-box.jconfirm-type-blue {
    border-top: solid 7px #3498db;
    -webkit-animation-name: type-blue;
    animation-name: type-blue;
}
.jconfirm .jconfirm-box.jconfirm-type-green {
    border-top: solid 7px #2ecc71;
    -webkit-animation-name: type-green;
    animation-name: type-green;
}
.jconfirm .jconfirm-box.jconfirm-type-red {
    border-top: solid 7px #e74c3c;
    -webkit-animation-name: type-red;
    animation-name: type-red;
}
.jconfirm .jconfirm-box.jconfirm-type-orange {
    border-top: solid 7px #f1c40f;
    -webkit-animation-name: type-orange;
    animation-name: type-orange;
}
.jconfirm .jconfirm-box.jconfirm-type-purple {
    border-top: solid 7px #9b59b6;
    -webkit-animation-name: type-purple;
    animation-name: type-purple;
}
.jconfirm .jconfirm-box.jconfirm-type-dark {
    border-top: solid 7px #34495e;
    -webkit-animation-name: type-dark;
    animation-name: type-dark;
}
.jconfirm .jconfirm-box.loading {
    height: 120px;
}
.jconfirm .jconfirm-box.loading:before {
    content: '';
    position: absolute;
    left: 0;
    background: white;
    right: 0;
    top: 0;
    bottom: 0;
    border-radius: 10px;
    z-index: 1;
}
.jconfirm .jconfirm-box.loading:after {
    opacity: 0.6;
    content: '';
    height: 30px;
    width: 30px;
    border: solid 3px transparent;
    position: absolute;
    left: 50%;
    margin-left: -15px;
    border-radius: 50%;
    -webkit-animation: jconfirm-spin 1s infinite linear;
    animation: jconfirm-spin 1s infinite linear;
    border-bottom-color: dodgerblue;
    top: 50%;
    margin-top: -15px;
    z-index: 2;
}
.jconfirm .jconfirm-box div.jconfirm-closeIcon {
    height: 20px;
    width: 20px;
    position: absolute;
    top: 10px;
    right: 10px;
    cursor: pointer;
    opacity: .6;
    text-align: center;
    font-size: 27px !important;
    line-height: 14px !important;
    display: none;
    z-index: 1;
}
.jconfirm .jconfirm-box div.jconfirm-closeIcon:empty {
    display: none;
}
.jconfirm .jconfirm-box div.jconfirm-closeIcon .fa {
    font-size: 16px;
}
.jconfirm .jconfirm-box div.jconfirm-closeIcon .glyphicon {
    font-size: 16px;
}
.jconfirm .jconfirm-box div.jconfirm-closeIcon .zmdi {
    font-size: 16px;
}
.jconfirm .jconfirm-box div.jconfirm-closeIcon:hover {
    opacity: 1;
}
.jconfirm .jconfirm-box div.jconfirm-title-c {
    display: block;
    font-size: 22px;
    line-height: 20px;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    cursor: default;
    padding-bottom: 15px;
}
.jconfirm .jconfirm-box div.jconfirm-title-c.jconfirm-hand {
    cursor: move;
}
.jconfirm .jconfirm-box div.jconfirm-title-c .jconfirm-icon-c {
    font-size: inherit;
    display: inline-block;
    vertical-align: middle;
}
.jconfirm .jconfirm-box div.jconfirm-title-c .jconfirm-icon-c i {
    vertical-align: middle;
}
.jconfirm .jconfirm-box div.jconfirm-title-c .jconfirm-icon-c:empty {
    display: none;
}
.jconfirm .jconfirm-box div.jconfirm-title-c .jconfirm-title {
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    font-size: inherit;
    font-family: inherit;
    display: inline-block;
    vertical-align: middle;
}
.jconfirm .jconfirm-box div.jconfirm-title-c .jconfirm-title:empty {
    display: none;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane {
    margin-bottom: 15px;
    height: auto;
    -webkit-transition: height 0.4s ease-in;
    transition: height 0.4s ease-in;
    display: inline-block;
    width: 100%;
    position: relative;
    overflow-x: hidden;
    overflow-y: auto;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane.no-scroll {
    overflow-y: hidden;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane::-webkit-scrollbar {
    width: 3px;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane::-webkit-scrollbar-track {
    background: rgba(0, 0, 0, 0.1);
}
.jconfirm .jconfirm-box div.jconfirm-content-pane::-webkit-scrollbar-thumb {
    background: #666;
    border-radius: 3px;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content {
    overflow: auto;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content img {
    max-width: 100%;
    height: auto;
}
.jconfirm .jconfirm-box div.jconfirm-content-pane .jconfirm-content:empty {
    display: none;
}
.jconfirm .jconfirm-box .jconfirm-buttons {
    padding-bottom: 11px;
}
.jconfirm .jconfirm-box .jconfirm-buttons > button {
    margin-bottom: 4px;
    margin-left: 2px;
    margin-right: 2px;
}
.jconfirm .jconfirm-box .jconfirm-buttons button {
    display: inline-block;
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 400;
    line-height: 1.42857143;
    text-align: center;
    white-space: nowrap;
    vertical-align: middle;
    -ms-touch-action: manipulation;
    touch-action: manipulation;
    cursor: pointer;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    user-select: none;
    border-radius: 4px;
    min-height: 1em;
    -webkit-transition: opacity 0.1s ease, background-color 0.1s ease, color 0.1s ease, background 0.1s ease, -webkit-box-shadow 0.1s ease;
    transition: opacity 0.1s ease, background-color 0.1s ease, color 0.1s ease, background 0.1s ease, -webkit-box-shadow 0.1s ease;
    transition: opacity 0.1s ease, background-color 0.1s ease, color 0.1s ease, box-shadow 0.1s ease, background 0.1s ease;
    transition: opacity 0.1s ease, background-color 0.1s ease, color 0.1s ease, box-shadow 0.1s ease, background 0.1s ease, -webkit-box-shadow 0.1s ease;
    -webkit-tap-highlight-color: transparent;
    border: none;
    background-image: none;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-blue {
    background-color: #3498db;
    color: #FFF;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-blue:hover {
    background-color: #2980b9;
    color: #FFF;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-green {
    background-color: #2ecc71;
    color: #FFF;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-green:hover {
    background-color: #27ae60;
    color: #FFF;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-red {
    background-color: #e74c3c;
    color: #FFF;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-red:hover {
    background-color: #c0392b;
    color: #FFF;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-orange {
    background-color: #f1c40f;
    color: #FFF;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-orange:hover {
    background-color: #f39c12;
    color: #FFF;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-default {
    background-color: #ecf0f1;
    color: #000;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-default:hover {
    background-color: #bdc3c7;
    color: #000;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-purple {
    background-color: #9b59b6;
    color: #FFF;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-purple:hover {
    background-color: #8e44ad;
    color: #FFF;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-dark {
    background-color: #34495e;
    color: #FFF;
    text-shadow: none;
    -webkit-transition: background .2s;
    transition: background .2s;
}
.jconfirm .jconfirm-box .jconfirm-buttons button.btn-dark:hover {
    background-color: #2c3e50;
    color: #FFF;
}
.jconfirm .jconfirm-box.jconfirm-type-red .jconfirm-title-c .jconfirm-icon-c {
    color: #e74c3c !important;
}
.jconfirm .jconfirm-box.jconfirm-type-blue .jconfirm-title-c .jconfirm-icon-c {
    color: #3498db !important;
}
.jconfirm .jconfirm-box.jconfirm-type-green .jconfirm-title-c .jconfirm-icon-c {
    color: #2ecc71 !important;
}
.jconfirm .jconfirm-box.jconfirm-type-purple .jconfirm-title-c .jconfirm-icon-c {
    color: #9b59b6 !important;
}
.jconfirm .jconfirm-box.jconfirm-type-orange .jconfirm-title-c .jconfirm-icon-c {
    color: #f1c40f !important;
}
.jconfirm .jconfirm-box.jconfirm-type-dark .jconfirm-title-c .jconfirm-icon-c {
    color: #34495e !important;
}
.jconfirm .jconfirm-clear {
    clear: both;
}
.jconfirm.jconfirm-rtl {
    direction: rtl;
}
.jconfirm.jconfirm-rtl div.jconfirm-closeIcon {
    left: 5px;
    right: auto;
}
.jconfirm.jconfirm-white .jconfirm-bg,
.jconfirm.jconfirm-light .jconfirm-bg {
    background-color: #444;
    opacity: .2;
}
.jconfirm.jconfirm-white .jconfirm-box,
.jconfirm.jconfirm-light .jconfirm-box {
    -webkit-box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    border-radius: 5px;
}
.jconfirm.jconfirm-white .jconfirm-box .jconfirm-title-c .jconfirm-icon-c,
.jconfirm.jconfirm-light .jconfirm-box .jconfirm-title-c .jconfirm-icon-c {
    margin-right: 8px;
    margin-left: 0px;
}
.jconfirm.jconfirm-white .jconfirm-box .jconfirm-buttons,
.jconfirm.jconfirm-light .jconfirm-box .jconfirm-buttons {
    float: right;
}
.jconfirm.jconfirm-white .jconfirm-box .jconfirm-buttons button,
.jconfirm.jconfirm-light .jconfirm-box .jconfirm-buttons button {
    text-transform: uppercase;
    font-size: 14px;
    font-weight: bold;
    text-shadow: none;
}
.jconfirm.jconfirm-white .jconfirm-box .jconfirm-buttons button.btn-default,
.jconfirm.jconfirm-light .jconfirm-box .jconfirm-buttons button.btn-default {
    -webkit-box-shadow: none;
    box-shadow: none;
    color: #333;
}
.jconfirm.jconfirm-white .jconfirm-box .jconfirm-buttons button.btn-default:hover,
.jconfirm.jconfirm-light .jconfirm-box .jconfirm-buttons button.btn-default:hover {
    background: #ddd;
}
.jconfirm.jconfirm-white.jconfirm-rtl .jconfirm-title-c .jconfirm-icon-c,
.jconfirm.jconfirm-light.jconfirm-rtl .jconfirm-title-c .jconfirm-icon-c {
    margin-left: 8px;
    margin-right: 0px;
}
.jconfirm.jconfirm-black .jconfirm-bg,
.jconfirm.jconfirm-dark .jconfirm-bg {
    background-color: darkslategray;
    opacity: .4;
}
.jconfirm.jconfirm-black .jconfirm-box,
.jconfirm.jconfirm-dark .jconfirm-box {
    -webkit-box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
    background: #444;
    border-radius: 5px;
    color: white;
}
.jconfirm.jconfirm-black .jconfirm-box .jconfirm-title-c .jconfirm-icon-c,
.jconfirm.jconfirm-dark .jconfirm-box .jconfirm-title-c .jconfirm-icon-c {
    margin-right: 8px;
    margin-left: 0px;
}
.jconfirm.jconfirm-black .jconfirm-box .jconfirm-buttons,
.jconfirm.jconfirm-dark .jconfirm-box .jconfirm-buttons {
    float: right;
}
.jconfirm.jconfirm-black .jconfirm-box .jconfirm-buttons button,
.jconfirm.jconfirm-dark .jconfirm-box .jconfirm-buttons button {
    border: none;
    background-image: none;
    text-transform: uppercase;
    font-size: 14px;
    font-weight: bold;
    text-shadow: none;
    -webkit-transition: background .1s;
    transition: background .1s;
    color: white;
}
.jconfirm.jconfirm-black .jconfirm-box .jconfirm-buttons button.btn-default,
.jconfirm.jconfirm-dark .jconfirm-box .jconfirm-buttons button.btn-default {
    -webkit-box-shadow: none;
    box-shadow: none;
    color: #fff;
    background: none;
}
.jconfirm.jconfirm-black .jconfirm-box .jconfirm-buttons button.btn-default:hover,
.jconfirm.jconfirm-dark .jconfirm-box .jconfirm-buttons button.btn-default:hover {
    background: #666;
}
.jconfirm.jconfirm-black.jconfirm-rtl .jconfirm-title-c .jconfirm-icon-c,
.jconfirm.jconfirm-dark.jconfirm-rtl .jconfirm-title-c .jconfirm-icon-c {
    margin-left: 8px;
    margin-right: 0px;
}
.jconfirm .jconfirm-box.hilight.jconfirm-hilight-shake {
    -webkit-animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
}
.jconfirm .jconfirm-box.hilight.jconfirm-hilight-glow {
    -webkit-animation: glow 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    animation: glow 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    -webkit-transform: translate3d(0, 0, 0);
    transform: translate3d(0, 0, 0);
}
@-webkit-keyframes shake {
    10%,
    90% {
    -webkit-transform: translate3d(-2px, 0, 0);
    transform: translate3d(-2px, 0, 0);
}
    20%,
    80% {
    -webkit-transform: translate3d(4px, 0, 0);
    transform: translate3d(4px, 0, 0);
}
    30%,
    50%,
    70% {
    -webkit-transform: translate3d(-8px, 0, 0);
    transform: translate3d(-8px, 0, 0);
}
    40%,
    60% {
    -webkit-transform: translate3d(8px, 0, 0);
    transform: translate3d(8px, 0, 0);
}
}
@keyframes shake {
    10%,
    90% {
    -webkit-transform: translate3d(-2px, 0, 0);
    transform: translate3d(-2px, 0, 0);
}
    20%,
    80% {
    -webkit-transform: translate3d(4px, 0, 0);
    transform: translate3d(4px, 0, 0);
}
    30%,
    50%,
    70% {
    -webkit-transform: translate3d(-8px, 0, 0);
    transform: translate3d(-8px, 0, 0);
}
    40%,
    60% {
    -webkit-transform: translate3d(8px, 0, 0);
    transform: translate3d(8px, 0, 0);
}
}
@-webkit-keyframes glow {
    0%,
    100% {
    -webkit-box-shadow: 0 0 0px red;
    box-shadow: 0 0 0px red;
}
    50% {
    -webkit-box-shadow: 0 0 30px red;
    box-shadow: 0 0 30px red;
}
}
@keyframes glow {
    0%,
    100% {
    -webkit-box-shadow: 0 0 0px red;
    box-shadow: 0 0 0px red;
}
    50% {
    -webkit-box-shadow: 0 0 30px red;
    box-shadow: 0 0 30px red;
}
}
/*Transition rules*/
.jconfirm {
    -webkit-perspective: 400px;
    perspective: 400px;
}
.jconfirm .jconfirm-box {
    opacity: 1;
    -webkit-transition-property: all;
    transition-property: all;
}
.jconfirm .jconfirm-box.jconfirm-animation-top,
.jconfirm .jconfirm-box.jconfirm-animation-left,
.jconfirm .jconfirm-box.jconfirm-animation-right,
.jconfirm .jconfirm-box.jconfirm-animation-bottom,
.jconfirm .jconfirm-box.jconfirm-animation-opacity,
.jconfirm .jconfirm-box.jconfirm-animation-zoom,
.jconfirm .jconfirm-box.jconfirm-animation-scale,
.jconfirm .jconfirm-box.jconfirm-animation-none,
.jconfirm .jconfirm-box.jconfirm-animation-rotate,
.jconfirm .jconfirm-box.jconfirm-animation-rotatex,
.jconfirm .jconfirm-box.jconfirm-animation-rotatey,
.jconfirm .jconfirm-box.jconfirm-animation-scaley,
.jconfirm .jconfirm-box.jconfirm-animation-scalex {
    opacity: 0;
}
.jconfirm .jconfirm-box.jconfirm-animation-rotate {
    -webkit-transform: rotate(90deg);
    transform: rotate(90deg);
}
.jconfirm .jconfirm-box.jconfirm-animation-rotatex {
    -webkit-transform: rotateX(90deg);
    transform: rotateX(90deg);
    -webkit-transform-origin: center;
    transform-origin: center;
}
.jconfirm .jconfirm-box.jconfirm-animation-rotatexr {
    -webkit-transform: rotateX(-90deg);
    transform: rotateX(-90deg);
    -webkit-transform-origin: center;
    transform-origin: center;
}
.jconfirm .jconfirm-box.jconfirm-animation-rotatey {
    -webkit-transform: rotatey(90deg);
    transform: rotatey(90deg);
    -webkit-transform-origin: center;
    transform-origin: center;
}
.jconfirm .jconfirm-box.jconfirm-animation-rotateyr {
    -webkit-transform: rotatey(-90deg);
    transform: rotatey(-90deg);
    -webkit-transform-origin: center;
    transform-origin: center;
}
.jconfirm .jconfirm-box.jconfirm-animation-scaley {
    -webkit-transform: scaley(1.5);
    transform: scaley(1.5);
    -webkit-transform-origin: center;
    transform-origin: center;
}
.jconfirm .jconfirm-box.jconfirm-animation-scalex {
    -webkit-transform: scalex(1.5);
    transform: scalex(1.5);
    -webkit-transform-origin: center;
    transform-origin: center;
}
.jconfirm .jconfirm-box.jconfirm-animation-top {
    -webkit-transform: translate(0px, -100px);
    transform: translate(0px, -100px);
}
.jconfirm .jconfirm-box.jconfirm-animation-left {
    -webkit-transform: translate(-100px, 0px);
    transform: translate(-100px, 0px);
}
.jconfirm .jconfirm-box.jconfirm-animation-right {
    -webkit-transform: translate(100px, 0px);
    transform: translate(100px, 0px);
}
.jconfirm .jconfirm-box.jconfirm-animation-bottom {
    -webkit-transform: translate(0px, 100px);
    transform: translate(0px, 100px);
}
.jconfirm .jconfirm-box.jconfirm-animation-zoom {
    -webkit-transform: scale(1.2);
    transform: scale(1.2);
}
.jconfirm .jconfirm-box.jconfirm-animation-scale {
    -webkit-transform: scale(0.5);
    transform: scale(0.5);
}
.jconfirm .jconfirm-box.jconfirm-animation-none {
    visibility: hidden;
}
.jconfirm.jconfirm-supervan .jconfirm-bg {
    background-color: rgba(54, 70, 93, 0.95);
}
.jconfirm.jconfirm-supervan .jconfirm-box {
    background-color: transparent;
}
.jconfirm.jconfirm-supervan .jconfirm-box.jconfirm-type-blue {
    border: none;
}
.jconfirm.jconfirm-supervan .jconfirm-box.jconfirm-type-green {
    border: none;
}
.jconfirm.jconfirm-supervan .jconfirm-box.jconfirm-type-red {
    border: none;
}
.jconfirm.jconfirm-supervan .jconfirm-box.jconfirm-type-orange {
    border: none;
}
.jconfirm.jconfirm-supervan .jconfirm-box.jconfirm-type-purple {
    border: none;
}
.jconfirm.jconfirm-supervan .jconfirm-box.jconfirm-type-dark {
    border: none;
}
.jconfirm.jconfirm-supervan .jconfirm-box div.jconfirm-closeIcon {
    color: white;
}
.jconfirm.jconfirm-supervan .jconfirm-box div.jconfirm-title-c {
    text-align: center;
    color: white;
    font-size: 28px;
    font-weight: normal;
}
.jconfirm.jconfirm-supervan .jconfirm-box div.jconfirm-title-c > * {
    padding-bottom: 25px;
}
.jconfirm.jconfirm-supervan .jconfirm-box div.jconfirm-title-c .jconfirm-icon-c {
    margin-right: 8px;
    margin-left: 0px;
}
.jconfirm.jconfirm-supervan .jconfirm-box div.jconfirm-content-pane {
    margin-bottom: 25px;
}
.jconfirm.jconfirm-supervan .jconfirm-box div.jconfirm-content {
    text-align: center;
    color: white;
}
.jconfirm.jconfirm-supervan .jconfirm-box .jconfirm-buttons {
    text-align: center;
}
.jconfirm.jconfirm-supervan .jconfirm-box .jconfirm-buttons button {
    font-size: 16px;
    border-radius: 2px;
    background: #303f53;
    text-shadow: none;
    border: none;
    color: white;
    padding: 10px;
    min-width: 100px;
}
.jconfirm.jconfirm-supervan.jconfirm-rtl .jconfirm-box div.jconfirm-title-c .jconfirm-icon-c {
    margin-left: 8px;
    margin-right: 0px;
}
.jconfirm.jconfirm-material .jconfirm-bg {
    background-color: rgba(0, 0, 0, 0.67);
}
.jconfirm.jconfirm-material .jconfirm-box {
    background-color: white;
    -webkit-box-shadow: 0 7px 8px -4px rgba(0, 0, 0, 0.2), 0 13px 19px 2px rgba(0, 0, 0, 0.14), 0 5px 24px 4px rgba(0, 0, 0, 0.12);
    box-shadow: 0 7px 8px -4px rgba(0, 0, 0, 0.2), 0 13px 19px 2px rgba(0, 0, 0, 0.14), 0 5px 24px 4px rgba(0, 0, 0, 0.12);
    padding: 30px 25px 10px 25px;
}
.jconfirm.jconfirm-material .jconfirm-box .jconfirm-title-c .jconfirm-icon-c {
    margin-right: 8px;
    margin-left: 0px;
}
.jconfirm.jconfirm-material .jconfirm-box div.jconfirm-closeIcon {
    color: rgba(0, 0, 0, 0.87);
}
.jconfirm.jconfirm-material .jconfirm-box div.jconfirm-title-c {
    color: rgba(0, 0, 0, 0.87);
    font-size: 22px;
    font-weight: bold;
}
.jconfirm.jconfirm-material .jconfirm-box div.jconfirm-content {
    color: rgba(0, 0, 0, 0.87);
}
.jconfirm.jconfirm-material .jconfirm-box .jconfirm-buttons {
    text-align: right;
}
.jconfirm.jconfirm-material .jconfirm-box .jconfirm-buttons button {
    text-transform: uppercase;
    font-weight: 500;
}
.jconfirm.jconfirm-material.jconfirm-rtl .jconfirm-title-c .jconfirm-icon-c {
    margin-left: 8px;
    margin-right: 0px;
}
.jconfirm.jconfirm-bootstrap .jconfirm-bg {
    background-color: rgba(0, 0, 0, 0.21);
}
.jconfirm.jconfirm-bootstrap .jconfirm-box {
    background-color: white;
    -webkit-box-shadow: 0 3px 8px 0px rgba(0, 0, 0, 0.2);
    box-shadow: 0 3px 8px 0px rgba(0, 0, 0, 0.2);
    border: solid 1px rgba(0, 0, 0, 0.4);
    padding: 15px 0 0;
}
.jconfirm.jconfirm-bootstrap .jconfirm-box .jconfirm-title-c .jconfirm-icon-c {
    margin-right: 8px;
    margin-left: 0px;
}
.jconfirm.jconfirm-bootstrap .jconfirm-box div.jconfirm-closeIcon {
    color: rgba(0, 0, 0, 0.87);
}
.jconfirm.jconfirm-bootstrap .jconfirm-box div.jconfirm-title-c {
    color: rgba(0, 0, 0, 0.87);
    font-size: 22px;
    font-weight: bold;
    padding-left: 15px;
    padding-right: 15px;
}
.jconfirm.jconfirm-bootstrap .jconfirm-box div.jconfirm-content {
    color: rgba(0, 0, 0, 0.87);
    padding: 0px 15px;
}
.jconfirm.jconfirm-bootstrap .jconfirm-box .jconfirm-buttons {
    text-align: right;
    padding: 10px;
    margin: -5px 0 0px;
    border-top: solid 1px #ddd;
    overflow: hidden;
    border-radius: 0 0 4px 4px;
}
.jconfirm.jconfirm-bootstrap .jconfirm-box .jconfirm-buttons button {
    font-weight: 500;
}
.jconfirm.jconfirm-bootstrap.jconfirm-rtl .jconfirm-title-c .jconfirm-icon-c {
    margin-left: 8px;
    margin-right: 0px;
}
.jconfirm.jconfirm-modern .jconfirm-bg {
    background-color: slategray;
    opacity: .6;
}
.jconfirm.jconfirm-modern .jconfirm-box {
    background-color: white;
    -webkit-box-shadow: 0 7px 8px -4px rgba(0, 0, 0, 0.2), 0 13px 19px 2px rgba(0, 0, 0, 0.14), 0 5px 24px 4px rgba(0, 0, 0, 0.12);
    box-shadow: 0 7px 8px -4px rgba(0, 0, 0, 0.2), 0 13px 19px 2px rgba(0, 0, 0, 0.14), 0 5px 24px 4px rgba(0, 0, 0, 0.12);
    padding: 30px 30px 15px;
}
.jconfirm.jconfirm-modern .jconfirm-box div.jconfirm-closeIcon {
    color: rgba(0, 0, 0, 0.87);
    top: 15px;
    right: 15px;
}
.jconfirm.jconfirm-modern .jconfirm-box div.jconfirm-title-c {
    color: rgba(0, 0, 0, 0.87);
    font-size: 24px;
    font-weight: bold;
    text-align: center;
    margin-bottom: 10px;
}
.jconfirm.jconfirm-modern .jconfirm-box div.jconfirm-title-c .jconfirm-icon-c {
    -webkit-transition: -webkit-transform .5s;
    transition: -webkit-transform .5s;
    transition: transform .5s;
    transition: transform .5s, -webkit-transform .5s;
    -webkit-transform: scale(0);
    transform: scale(0);
    display: block;
    margin-right: 0px;
    margin-left: 0px;
    margin-bottom: 10px;
    font-size: 69px;
    color: #aaa;
}
.jconfirm.jconfirm-modern .jconfirm-box div.jconfirm-content {
    text-align: center;
    font-size: 15px;
    color: #777;
    margin-bottom: 25px;
}
.jconfirm.jconfirm-modern .jconfirm-box .jconfirm-buttons {
    text-align: center;
}
.jconfirm.jconfirm-modern .jconfirm-box .jconfirm-buttons button {
    font-weight: bold;
    text-transform: uppercase;
    -webkit-transition: background .1s;
    transition: background .1s;
    padding: 10px 20px;
}
.jconfirm.jconfirm-modern .jconfirm-box .jconfirm-buttons button + button {
    margin-left: 4px;
}
.jconfirm.jconfirm-modern.jconfirm-open .jconfirm-box .jconfirm-title-c .jconfirm-icon-c {
    -webkit-transform: scale(1);
    transform: scale(1);
}