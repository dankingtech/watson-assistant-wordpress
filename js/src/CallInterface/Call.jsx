import React, { Component } from 'react';

export default class Call extends Component {
  constructor(props) {
    super(props);
    this.state = {
      log: 'Connecting you to an agent...'
    }
  }

  componentDidMount() {
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
      setTimeout(this.props.endCall, 1000);
    });

    Twilio.Device.ready(this.startCall.bind(this));
  }

  startCall() {
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

  render() {
    return (
      <div id='controls'>
        <p>{this.state.log}</p>
        <button onClick={this.disconnect.bind(this)}>Hang Up</button>
      </div>
    );
  }
}