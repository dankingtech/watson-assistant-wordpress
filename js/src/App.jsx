import React, { Component } from 'react';
import Draggable from 'react-draggable';
import { TransitionGroup } from 'react-transition-group';

import ChatBox from './ChatBox.jsx';
import Fab from './Fab.jsx';

export default class App extends Component {
  constructor(props) {
    super(props);

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('chat_bot_state') !== null)
    {
      this.state = JSON.parse(sessionStorage.getItem('chat_bot_state'));
    } else {
      this.state = {
        minimized: props.minimized,
        position: {x: 0, y: 0}
      };
    }

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('chat_bot_position') !== null)
    {
      let pos = JSON.parse(sessionStorage.getItem('chat_bot_position'));
      this.state.position = {
        x: pos.x * window.innerWidth,
        y: pos.y * window.innerHeight
      };
    }
  }

  toggleMinimize(e) {
    e.preventDefault();
    this.setState({minimized: !this.state.minimized});
  }

  setMessage(e) {
    this.setState({newMessage: e.target.value});
  }

  startDragging(e) {
    e.preventDefault();

    this.setState({
      dragging: true
    })
  }

  savePosition(e, data) {
    this.setState({
      dragging: false
    });

    if (Math.sqrt(Math.pow(data.x - this.state.position.x, 2) +  Math.pow(data.y - this.state.position.y, 2)) < 3) {
      this.toggleMinimize(e);
    }

    this.setState({position: {x: data.x, y: data.y}});

    if (typeof(sessionStorage) !== 'undefined') {
      sessionStorage.setItem('chat_bot_position', JSON.stringify({
        x: data.x / window.innerWidth,
        y: data.y / window.innerHeight}));
    }
  }

  render() {
    return (
      <div>
        <Draggable
          handle='#watson-header'
          onStart={this.startDragging.bind(this)}
          onStop={this.savePosition.bind(this)}
          position={this.state.minimized ? {x: 0, y: 0} : this.state.position}
        >
          <TransitionGroup
            id='watson-float'
            class={!this.state.dragging && 'animated'}
            style={{opacity: this.state.minimized ? 0 : 1}}
          >
            {!this.state.minimized && 
              <ChatBox 
                  minimize={this.toggleMinimize.bind(this)}
                  position={this.props.position}
                  title={this.props.title} 
              />
            }
          </TransitionGroup>
        </Draggable>
        <TransitionGroup
          id='watson-fab-float'
          class='animated'
          style={{opacity: this.state.minimized ? 1 : 0}}
        >
          {this.state.minimized && <Fab openChat={this.toggleMinimize.bind(this)} />}
        </TransitionGroup>
      </div>
    );
  }
}
