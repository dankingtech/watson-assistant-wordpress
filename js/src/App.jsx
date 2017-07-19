import React, { Component } from 'react';
import Draggable from 'react-draggable';
import { TransitionGroup } from 'react-transition-group';

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
        minimized: props.minimized,
        position: {x: 0, y: 0}
      };
    }
  }

  componentDidUpdate(prevProps, prevState) {
    if (this.state != prevState && typeof(sessionStorage) !== 'undefined') {
      sessionStorage.setItem('watson_bot_window_state', JSON.stringify(this.state));

      if (this.state.minimized != prevState.minimized) {
        document.body.style.overflow = this.state.minimized ? 'scroll' : 'hidden';
      }
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
    } else {
      this.setState({position: {x: data.x, y: data.y}});
    }
  }

  render() {
    return (
      <div>
        <Draggable
          handle='#watson-header'
          bounds={window.matchMedia("(max-width:768px)").matches && {left: 0, top: 0, right: 0, bottom: 0}}
          onStart={this.startDragging.bind(this)}
          onStop={this.savePosition.bind(this)}
          position={this.state.minimized ? {x: 0, y: 0} : this.state.position}
        >
          <TransitionGroup
            id='watson-float'
            class={!this.state.dragging && 'animated'}
            style={this.state.minimized && {opacity: 0 , visibility: 'hidden'}}
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
