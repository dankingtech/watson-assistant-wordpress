import React, { Component } from 'react';
import { TransitionGroup } from 'react-transition-group';

import Call from './Call.jsx';

export default class CallInterface extends Component {
  constructor(props) {
    super(props);
    this.state = {
      calling: false,
      log: 'Connecting you to an agent...',
      hasToken: false
    };
  }

  connect() {
    this.setState({
      onPhone: true
    })

    Twilio.Device.connect({ number: '+16473034238' });
    this.setState({log: 'Calling Agent...'})
  }

  disconnect() {
    Twilio.Device.disconnectAll();
    this.setState({log: 'Call ended.'});
  }

  startCall() {
    this.setState({
      calling: true
    });

    if (!this.state.hasToken) {
      fetch('?rest_route=/watsonconv/v1/twilio-token', {
        headers: {
          'Content-Type': 'application/json'
        },
        method: 'GET'
      }).then(response => {
        if (!response.ok) {
            throw Error('Unable to fetch token.');
        }
        return response.json();
      }).then(body => {
        Twilio.Device.setup(body.token);
      }).catch(error => {
        console.log(error);
        this.setState({log: 'Call failed.'});
      });

      Twilio.Device.disconnect(() =>{
        this.setState({
          onPhone: false,
          log: 'Call ended.'
        });
        setTimeout(this.endCall.bind(this), 1000);
      });

      Twilio.Device.ready(() => {
        this.setState({hasToken: true});
        this.connect();
      });
    } else {
      this.connect();
    }
  }

  endCall() {
    this.setState({calling: false});
  }

  render() {
    return <span id='call-interface'>
        {this.state.calling ? 
        <div id='controls'>
          <p>{this.state.log}</p>
          <button onClick={this.disconnect.bind(this)}>Hang Up</button>
        </div> 
        :
        <div id='controls'>
          <p style={{'line-height': '2.7em'}}>
            Dial <a href='tel:647-303-4238'>647-303-4238</a> <br/>
            or
          </p>
          <button onClick={this.startCall.bind(this)}>
            Start Call Here
          </button>
        </div>}
      </span>
  }
}