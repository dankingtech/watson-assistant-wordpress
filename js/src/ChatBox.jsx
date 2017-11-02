import React, { Component } from 'react';
import Draggable from 'react-draggable';
import { TransitionGroup } from 'react-transition-group';
import Message from './Message.jsx';

export default class ChatBox extends Component {
  constructor(props) {
    super(props);

    if (typeof(sessionStorage) !== 'undefined' &&
        sessionStorage.getItem('watson_bot_state'))
    {
      this.state = JSON.parse(sessionStorage.getItem('watson_bot_state'));
    } else {
      this.state = {
        messages: [],
        newMessage: '',
        context: null
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
    if (prevState.messages.length !== this.state.messages.length) {
      if (typeof(sessionStorage) !== 'undefined') {
        sessionStorage.setItem('watson_bot_state', JSON.stringify(this.state))
      }
      // Ensure that chat box stays scrolled to bottom
      if (typeof(this.messageList) !== 'undefined') {
        jQuery(this.messageList).stop().animate({scrollTop: this.messageList.scrollHeight});
      }
    }
  }

  componentWillLeave(callback) {
    setTimeout(callback, 300);
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

  reset() {
    this.setState({
      messages: [],
      newMessage: '',
      context: null
    });
    
    this.sendMessage();
  }

  render() {
    var position = this.props.position || ['bottom', 'right'];

    return (
      <div id='watson-box' className='drop-shadow animated'>
        <div
          id='watson-header'
          class='watson-font'
        >
          <span className={`dashicons dashicons-arrow-${
              position[0] == 'bottom' ? 'down' : 'up'
            }-alt2 popup-control`}></span>
          <span className={`dashicons dashicons-phone header-button`}></span>
          <div className='overflow-hidden watson-font'>{this.props.title}</div>
        </div>
        <div id='message-container'>
          <div id='messages' ref={div => {this.messageList = div}}>
            <div style={{'text-align': 'right', 'margin-top': -5, 'margin-bottom': 5, 'margin-left': 10}} className='watson-font'>
              <a style={{'font-size': '0.85em'}} onClick={this.reset.bind(this)}>Clear Messages</a>
            </div>
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
    );
  }
}
