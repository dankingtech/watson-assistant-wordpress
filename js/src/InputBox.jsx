import React, { Component } from 'react';

export default class InputBox extends Component {
  constructor(props) {
    super(props);

    this.state = {
      message: ''
    };
  }

  setMessage(e) {
    this.setState({message: e.target.value});
  }

  submitMessage(e) {
    e.preventDefault();

    if (this.state.message === '') {
      return false;
    }

    console.log('lol');

    this.props.sendMessage(this.state.message);

    this.setState({
      message: ''
    });
  }

  render() {
    return (
      <form action='' className='message-form watson-font' onSubmit={this.submitMessage.bind(this)}>
        <input
          className='message-input watson-font'
          type='text'
          placeholder='Type a message'
          value={this.state.message}
          onChange={this.setMessage.bind(this)}
        />
        <input type='submit' style={{width: 0, height: 0, opacity: 0}} />
      </form>
    );
  }
}