import React, { Component } from 'react';
import Draggable from 'react-draggable';
import { TransitionGroup } from 'react-transition-group';
import qs from 'querystringify';

import ChatBox from './ChatBox.jsx';
import Fab from './Fab.jsx';

export default class App extends Component {
  constructor(props) {
    super(props);

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('watson_bot_window_state'))
    {
      this.state = JSON.parse(sessionStorage.getItem('watson_bot_window_state'));
    } else {
      this.state = {
        minimized: props.isMobile || (watsonconvSettings.minimized === 'yes'),
        position: {x: 0, y: 0}
      };
    }

    let params = qs.parse(window.location.search);

    if (params.hasOwnProperty('chat_min')) {
      if (params.chat_min == 'true' || params.chat_min == 'yes') {
        this.state.minimized = true;
      } else if (params.chat_min == 'false' || params.chat_min == 'no') {
        this.state.minimized = false;
      }
    }
  }

  componentDidUpdate(prevProps, prevState) {
    this.saveState();

    if (this.state.minimized != prevState.minimized && this.props.isMobile) {
      document.body.style.overflow = this.state.minimized ? 'scroll' : 'hidden';
    }
  }

  toggleMinimize(e) {
    e.preventDefault();
    this.setState({minimized: !this.state.minimized});
  }

  startDragging(e) {
    e.preventDefault();

    this.setState({
      animated: true
    })
  }

  saveState() {
    if (typeof(sessionStorage) !== 'undefined') {
      sessionStorage.setItem('watson_bot_state', JSON.stringify(this.state))
    }
  }

  savePosition(e, data) {
    if (Math.sqrt(Math.pow(data.x - this.state.position.x, 2) +  Math.pow(data.y - this.state.position.y, 2)) < 3) {
      this.setState({animated: false});
      this.toggleMinimize(e);
    } else {
      this.setState(
        {position: {x: data.x, y: data.y}}
      );
    }
  }

  render(props, {minimized, animated}) {

    return (
      <div>
        <Draggable
          handle='#watson-header'
          cancel='#watson-header .header-button'
          onStart={this.startDragging.bind(this)}
          onStop={this.savePosition.bind(this)}
        >
          <TransitionGroup
            id='watson-float'
            class={!animated && 'animated'}
            style={minimized && {opacity: 0 , visibility: 'hidden'}}
          >
            <ChatBox isMinimized={minimized} />
          </TransitionGroup>
        </Draggable>
        <TransitionGroup
          id='watson-fab-float'
          class='animated'
          style={!minimized && {opacity: 0 , visibility: 'hidden'}}
        >
          {minimized && <Fab openChat={this.toggleMinimize.bind(this)} />}
        </TransitionGroup>
      </div>
    );
  }
}
