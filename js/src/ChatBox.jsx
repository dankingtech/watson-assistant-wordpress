import React, { Component } from 'react';
import Draggable from 'react-draggable';
import { Collapse } from 'react-collapse';
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
        closed: false
      };
    }

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('chat_bot_position') !== null)
    {
      this.savedPosition = JSON.parse(sessionStorage.getItem('chat_bot_position'));
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

  toggleMinimize() {
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

  savePosition(e, data) {
    if (typeof(sessionStorage) !== 'undefined') {
      let {top, left} = data.node.getBoundingClientRect();
      sessionStorage.setItem('chat_bot_position', JSON.stringify({
        top: (top / window.innerHeight) * 100,
        left: (left / window.innerWidth) * 100
      }));
    }
  }

  render() {
    return (this.state.messages.length != 0) && !this.state.closed && (
      <Draggable
        handle='.popup-head'
        cancel={this.state.minimized ? '.popup-head' : ''}
        onStart={e => e.preventDefault()}
        onStop={this.savePosition}
      >
        <span
          style={this.savedPosition && {
            top: `${this.savedPosition.top}%`,
            left: `${this.savedPosition.left}%`,
            bottom: 'auto',
            right: 'auto'
          }}
          className='popup-box-wrapper'
        >
          <div className='popup-box'>
            <div
              className='popup-head'
              style={this.state.minimized ? {cursor: 'pointer'} : {cursor: 'move'}}
              onClick={this.state.minimized && this.toggleMinimize.bind(this)}
            >
              Watson
              <span className='dashicons dashicons-no-alt popup-control'
                onClick={this.closeChat.bind(this)}></span>
              <span className={`dashicons dashicons-arrow-${
                  (this.props.bottom && !this.savedPosition) != this.state.minimized ? 'down' : 'up'
                }-alt2 popup-control`}
                onClick={!this.state.minimized && this.toggleMinimize.bind(this)}></span>
            </div>
            <Collapse isOpened={!this.state.minimized}>
              <div className='message-container'>
                <div className='messages' ref={div => {this.messageList = div}}>
                  {this.state.messages.map(
                    (message, index) => <Message message={message} key={index} />
                  )}
                </div>
              </div>
              <form className='message-form' onSubmit={this.submitMessage.bind(this)}>
                <input
                  className='message-input'
                  type='text'
                  placeholder='Type a message'
                  value={this.state.newMessage}
                  onChange={this.setMessage.bind(this)}
                />
              </form>
            </Collapse>
          </div>
        </span>
      </Draggable>
    );
  }
}
