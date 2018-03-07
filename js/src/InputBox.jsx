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

    this.props.sendMessage(this.state.message);

    this.setState({
      message: ''
    });
  }

  render() {
    let showSendBtn = (watsonconvSettings.showSendBtn === 'yes');

    return (
      <form action='' className='message-form watson-font' onSubmit={this.submitMessage.bind(this)}>
        <input
          className='message-input watson-font'
          type='text'
          placeholder='Type a message'
          value={this.state.message}
          onChange={this.setMessage.bind(this)}
        />
        {showSendBtn && 
          <button type='submit' id='message-send'>
            <div>
              <svg xmlns="http://www.w3.org/2000/svg" 
                viewBox="0 0 48 48" 
                fill="white"
              >
                <path d="M4.02 42L46 24 4.02 6 4 20l30 4-30 4z"/>
              </svg>
            </div>
          </button>
        }
      </form>
    );
  }
}