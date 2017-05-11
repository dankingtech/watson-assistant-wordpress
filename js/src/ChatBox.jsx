import React, { Component } from 'react';

export default class ChatBox extends Component {
  constructor(props) {
    super(props);

    this.state = {
      messages: [],
      newMessage: '',
      context: null
    };
  }

  componentDidMount(props) {
    // Start conversation.
    this.sendMessage();
  }

  submitMessage(e) {
    this.sendMessage();
    this.setState({
      newMessage: '',
      messages: this.state.messages.concat({from: 'user', text: this.state.newMessage})
    });

    e.preventDefault();
  }

  sendMessage() {
    fetch('/wp-json/watsonconv/v1/message', {
      headers: {
        'Content-Type': 'application/json'
      },
      method: 'POST',
      body: JSON.stringify({
        input: {text: this.state.newMessage},
        context: this.state.context
      })
    }).then(response => {
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

  renderMessage(message, index) {
    return (
      <div key={`message${index}`} className={`${message.from}-message`}>
        {message.text}
      </div>
    );
  }

  render() {
    return (
      <div className='popup-box'>
        <div className='popup-head'>Watson</div>
        <div className='popup-messages'>
          {this.state.messages.map(this.renderMessage.bind(this))}
        </div>
        <form onSubmit={this.submitMessage.bind(this)} className='popup-message-form'>
          <input
            className='popup-message-input'
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
