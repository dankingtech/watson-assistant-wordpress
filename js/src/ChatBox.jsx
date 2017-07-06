import React, { Component } from 'react';
import Draggable from 'react-draggable';
import Message from './Message.jsx';

export default class ChatBox extends Component {
  constructor(props) {
    super(props);

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('chat_bot_state') !== null)
    {
      this.state = JSON.parse(sessionStorage.getItem('chat_bot_state'));
    } else {
      this.state = {
        messages: [],
        newMessage: '',
        context: null,
        minimized: props.minimized,
        position: {x: 0, y: 0},
        dragging: false
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

  componentDidMount(props) {
    // If conversation already exists, scroll to bottom, otherwise start conversation.
    if (this.state.messages.length === 0) {
      this.sendMessage();
    } else if (typeof(this.messageList) !== 'undefined') {
      this.messageList.scrollTop = this.messageList.scrollHeight;
    }
  }

  componentDidUpdate(prevProps, prevState) {
    if (typeof(sessionStorage) !== 'undefined') {
      sessionStorage.setItem('chat_bot_state', JSON.stringify(this.state))
    }

    // Ensure that chat box stays scrolled to bottom
    if (typeof(this.messageList) !== 'undefined') {
      if (prevState.messages.length !== this.state.messages.length) {
        jQuery(this.messageList).stop().animate({scrollTop: this.messageList.scrollHeight});
      } else if (prevState.minimized != this.state.minimized) {
        this.messageList.scrollTop = this.messageList.scrollHeight;
      }
    }
  }

  toggleMinimize(e) {
    e.preventDefault();
    this.setState({minimized: !this.state.minimized});
  }

  closeChat() {
    this.setState({closed: true});
  }

  submitMessage(e) {
    e.preventDefault();

    if (this.state.newMessage === '') {
      return false;
    }

    this.sendMessage();
    this.setState({
      newMessage: '',
      messages: this.state.messages.concat({from: 'user', text: this.state.newMessage})
    });
  }

  sendMessage() {
    fetch('?rest_route=/watsonconv/v1/message', {
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'POST',
      body: JSON.stringify({
        input: {text: this.state.newMessage},
        context: this.state.context
      })
    }).then(response => {
      if (!response.ok) {
          throw Error('Message could not be sent.');
      }
      return response.json();
    }).then(body => {
      this.setState({
        context: body.context,
        messages: this.state.messages.concat({from: 'watson', text: body.output.text})
      });
    }).catch(error => {
      console.log(error);
    })
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

    if (typeof(sessionStorage) !== 'undefined') {
      this.setState({position: {x: data.x, y: data.y}});
      sessionStorage.setItem('chat_bot_position', JSON.stringify({
        x: data.x / window.innerWidth,
        y: data.y / window.innerHeight}));
    }
  }

  render() {
    return (this.state.messages.length != 0) && (
      <div>
        <Draggable
          handle='#watson-header'
          onStart={this.startDragging.bind(this)}
          onStop={this.savePosition.bind(this)}
          position={this.state.minimized ? {x: 0, y: 0} : this.state.position}
        >
          <span
            id='watson-float'
            class={!this.state.dragging && 'animated'}
            style={{opacity: this.state.minimized ? 0 : 1}}
          >
            <div id='watson-box' className='drop-shadow animated'>
              <div
                id='watson-header'
                class='watson-font'
              >
                <span className={`dashicons dashicons-arrow-${
                    this.props.position[0] == 'bottom' ? 'down' : 'up'
                  }-alt2 popup-control`}
                  onClick={this.toggleMinimize.bind(this)}></span>
                <div className='overflow-hidden watson-font'>{this.props.title}</div>
              </div>
              <div id='message-container'>
                <div id='messages' ref={div => {this.messageList = div}}>
                  {this.state.messages.map(
                    (message, index) => <Message message={message} key={index} />
                  )}
                </div>
              </div>
              <form className='message-form watson-font' onSubmit={this.submitMessage.bind(this)}>
                <input
                  className='message-input watson-font'
                  type='text'
                  placeholder='Type a message'
                  value={this.state.newMessage}
                  onChange={this.setMessage.bind(this)}
                />
              </form>
            </div>
          </span>
        </Draggable>
        <div 
          id='watson-fab' 
          class='drop-shadow animated' 
          style={{opacity: this.state.minimized ? 1 : 0}} 
          onClick={this.toggleMinimize.bind(this)}
        >
          <span id='watson-fab-icon' class='dashicons dashicons-format-chat'></span>
        </div>
      </div>
    );
  }
}
